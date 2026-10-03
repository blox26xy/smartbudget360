(() => {
  const body = document.body;
  const toggle = document.getElementById('sidebarToggle');
  if (toggle) toggle.addEventListener('click', () => {
    if (window.innerWidth < 992) body.classList.toggle('sidebar-mobile-open');
    else body.classList.toggle('sidebar-collapsed');
  });

  const globalSearch = document.getElementById('globalSearch');
  if (globalSearch) globalSearch.addEventListener('input', function(){
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('table tbody tr').forEach(row => {
      row.style.display = !q || row.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
  });

  if (window.jQuery && $.fn.DataTable) {
    $('.data-table').each(function(){
      if ($.fn.DataTable.isDataTable(this)) return;
      new DataTable(this, {
        pageLength: 10,
        order: [],
        layout: { topStart: { buttons: ['copy','csv','excel','pdf','print'] }, topEnd: 'search', bottomStart: 'info', bottomEnd: 'paging' },
        buttons: { buttons: [
          {extend:'copy', className:'btn btn-sm btn-light'},
          {extend:'csv', className:'btn btn-sm btn-light'},
          {extend:'excel', className:'btn btn-sm btn-light'},
          {extend:'pdf', className:'btn btn-sm btn-light', orientation:'landscape', pageSize:'A4'},
          {extend:'print', className:'btn btn-sm btn-light'}
        ]}
      });
    });
  }

  document.querySelectorAll('[data-confirm]').forEach(el => el.addEventListener('click', function(e){
    e.preventDefault();
    const href = this.getAttribute('href');
    Swal.fire({title:'Are you sure?', text:this.dataset.confirm || 'This action cannot be undone.', icon:'warning', showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Yes, continue'}).then(r => { if(r.isConfirmed) window.location.href = href; });
  }));
})();
