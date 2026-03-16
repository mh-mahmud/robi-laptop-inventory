<div id="page_footer" class="text-center">
	<font color="#EF1B24"><?php echo $this->webspice->settings()->copyright_text; ?></font>
</div>
<style>
	.dataTables_filter{
		display:none;
	}
</style>
<script>
$(document).ready(function(){
	$('.page_caption, .page-body-title').css('color','#EF1B24');
	$(":checkbox").labelauty({ label: false });

   var asInitVals = new Array();
	 var usersTable =   $('#example').DataTable({
	 "stateSave": false,
	 "pagingType": "full_numbers",
	 "scrollX": "100%",
	 "paging": true,
	 "dom": 'C<"clear">lfrtip',
	 "scrollCollapse": true,
	 "pageLength": 15,
	 "bLengthChange": false,
	 "lengthMenu" : [[5,10,15,20,30,-1],[5,10,15,20,30,"All"]]
	 });
  
	//Apply the filter
	   usersTable.columns().eq( 0 ).each( function ( colIdx ) {
			var title = usersTable.column( colIdx ).header();		    	 
		   $( '.dataTables_filter', usersTable.column( colIdx ).footer() ).on( 'keyup change', function () {
			   usersTable
					.column( colIdx )
					.search( this.value )
					.draw();
			} );
	   });
	
	   new $.fn.DataTable.FixedColumns( usersTable, {
			leftColumns: 2,
			rightColumns:1,
			"sScrollX":"100%",
			"sScrollXInner":"100%"	
	 });
	 
	 
});
</script>