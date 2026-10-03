<?php
function build_report(string $type, string $from, string $to, int $departmentId = 0): array
{
    $pdo = db();
    switch ($type) {
        case 'expense':
            $sql = "SELECT e.expense_code Code,e.expense_date Date,d.name Department,c.name Category,e.description Description,e.amount Amount,e.status Status,CONCAT(u.first_name,' ',u.last_name) 'Requested By' FROM expenses e JOIN departments d ON d.id=e.department_id JOIN budget_categories c ON c.id=e.category_id JOIN users u ON u.id=e.requested_by WHERE e.expense_date BETWEEN ? AND ?";
            $params = [$from,$to];
            if ($departmentId) { $sql .= ' AND e.department_id=?'; $params[]=$departmentId; }
            $sql .= ' ORDER BY e.expense_date DESC';
            $s=$pdo->prepare($sql);$s->execute($params);
            return ['title'=>'Expense Report','rows'=>$s->fetchAll()];
        case 'sales':
            $sql="SELECT DATE_FORMAT(sr.sales_month,'%Y-%m') Month,CONCAT(u.first_name,' ',u.last_name) 'Sales Person',d.name Department,sr.sales_target 'Sales Target',sr.actual_sales 'Actual Sales',ROUND(sr.actual_sales/NULLIF(sr.sales_target,0)*100,2) 'Achievement %' FROM sales_records sr JOIN users u ON u.id=sr.salesperson_id JOIN departments d ON d.id=sr.department_id WHERE sr.sales_month BETWEEN ? AND ?";
            $params=[date('Y-m-01',strtotime($from)),date('Y-m-01',strtotime($to))];if($departmentId){$sql.=' AND sr.department_id=?';$params[]=$departmentId;}$sql.=' ORDER BY sr.sales_month DESC, `Actual Sales` DESC';$s=$pdo->prepare($sql);$s->execute($params);return ['title'=>'Sales Performance Report','rows'=>$s->fetchAll()];
        case 'marketing':
            $sql="SELECT c.campaign_name Campaign,c.campaign_type Type,d.name Department,c.start_date 'Start Date',c.end_date 'End Date',c.budget Budget,c.actual_spending Spending,c.generated_sales 'Generated Sales',ROUND(CASE WHEN c.actual_spending>0 THEN ((c.generated_sales-c.actual_spending)/c.actual_spending)*100 ELSE 0 END,2) 'ROI %',c.status Status FROM campaigns c JOIN departments d ON d.id=c.department_id WHERE c.start_date<=? AND c.end_date>=?";$params=[$to,$from];if($departmentId){$sql.=' AND c.department_id=?';$params[]=$departmentId;}$sql.=' ORDER BY c.start_date DESC';$s=$pdo->prepare($sql);$s->execute($params);return ['title'=>'Marketing ROI Report','rows'=>$s->fetchAll()];
        case 'monthly':
            $s=$pdo->prepare("SELECT DATE_FORMAT(m.dt,'%Y-%m') Month,COALESCE((SELECT SUM(e.amount) FROM expenses e WHERE e.status='Approved' AND DATE_FORMAT(e.expense_date,'%Y-%m')=DATE_FORMAT(m.dt,'%Y-%m')),0) 'Approved Expenses',COALESCE((SELECT SUM(sr.actual_sales) FROM sales_records sr WHERE DATE_FORMAT(sr.sales_month,'%Y-%m')=DATE_FORMAT(m.dt,'%Y-%m')),0) 'Actual Sales',COALESCE((SELECT SUM(ce.amount) FROM campaign_expenses ce WHERE DATE_FORMAT(ce.expense_date,'%Y-%m')=DATE_FORMAT(m.dt,'%Y-%m')),0) 'Campaign Spending' FROM (SELECT DATE_FORMAT(expense_date,'%Y-%m-01') dt FROM expenses WHERE expense_date BETWEEN ? AND ? UNION SELECT sales_month dt FROM sales_records WHERE sales_month BETWEEN ? AND ? UNION SELECT DATE_FORMAT(expense_date,'%Y-%m-01') dt FROM campaign_expenses WHERE expense_date BETWEEN ? AND ?) m ORDER BY m.dt DESC");$s->execute([$from,$to,date('Y-m-01',strtotime($from)),date('Y-m-01',strtotime($to)),$from,$to]);return ['title'=>'Monthly Financial Report','rows'=>$s->fetchAll()];
        case 'budget':
        default:
            $year=(int)date('Y',strtotime($to));
            $sql="SELECT b.budget_year Year,d.name Department,c.name Category,SUM(b.allocated_amount) 'Allocated Amount',COALESCE((SELECT SUM(e.amount) FROM expenses e WHERE e.status='Approved' AND e.department_id=b.department_id AND e.category_id=b.category_id AND YEAR(e.expense_date)=b.budget_year),0) 'Approved Expenses',(SUM(b.allocated_amount)-COALESCE((SELECT SUM(e.amount) FROM expenses e WHERE e.status='Approved' AND e.department_id=b.department_id AND e.category_id=b.category_id AND YEAR(e.expense_date)=b.budget_year),0)) Balance FROM budgets b JOIN departments d ON d.id=b.department_id JOIN budget_categories c ON c.id=b.category_id WHERE b.budget_year=?";
            $params=[$year];
            if($departmentId){$sql.=' AND b.department_id=?';$params[]=$departmentId;}
            $sql.=' GROUP BY b.budget_year,b.department_id,d.name,b.category_id,c.name ORDER BY d.name,c.name';
            $s=$pdo->prepare($sql);$s->execute($params);
            return ['title'=>'Budget Summary Report','rows'=>$s->fetchAll()];
    }
}
