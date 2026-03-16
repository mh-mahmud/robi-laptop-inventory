<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $this->webspice->settings()->domain_name; ?>: Welcome</title>
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	<?php include("global.php"); ?>
<script class="include" type="text/javascript" src="<?php echo $url_prefix; ?>global/canvasjs/canvasjs.min.js"></script>
</head>

<body>
	<div id="wrapper">
		<div id="header_container"><?php include("header.php"); ?></div>
		
		<div id="page_index" class="main_container page_identifier">
			<div class="page_caption">Welcome</div>
			<div class="page_body" style="overflow:hidden">
				<h4 class="text-danger text-center page-body-title">Welcome to <?php echo $this->webspice->settings()->site_title; ?></h4><br />
				<div class="left_section">
<?php $stock_total = $this->db->query("SELECT * FROM TBL_STOCK")->num_rows(); ?>					
				<div class="total_laptop_chart">
					<div class="breadcrumb"><b>Total laptop. (Total <?php echo $stock_total; ?> )  </b><a class="btn btn-danger btn-xs btn_total_laptop_chart"> View as Table</a></div>
					<div id="totalchartContainer" style="height: 400px; width: 100%;"></div>
				</div>
				
				<div class="total_laptop_table">
					<div class="breadcrumb"><b>Total laptop. (Total <?php echo $stock_total; ?> )  </b> <a class="btn btn-danger btn-xs btn_total_laptop_table"> View as Chart</a></div>
				<table class="table table-bordered table-striped new_table">
					<tr>
						<th>SL</th>
						<th>Brand Name</th>
						<th>Model</th>
						<th>Stock</th>
					</tr>
					<?php $i=1; foreach($total_laptop as $k=>$v): ?>
					<tr>
						<td><?php echo $i; ?></td>
						<td><?php echo $v->BRAND; ?></td>
						<td><?php echo $v->MODEL; ?></td>
						<td><?php echo $v->STOCK; ?></td>
					</tr>
					<?php $i++; endforeach; ?>
				</table><br /><br />
				</div>
				<div style="overflow:auto">
					<div class="eol_laptop_chart">
						<div class="breadcrumb"><b>Laptop EOL Information. </b><a class="btn btn-danger btn-xs btn_eol_laptop_chart"> View as Table</a></div>
						<div id="eolchartContainer" style="height: 400px; width: 100%;"></div>
					</div>
					
					<div class="eol_laptop_table">
						<div class="breadcrumb"><b>Laptop EOL Information</b> <a class="btn btn-danger btn-xs btn_eol_laptop_table"> View as Chart</a></div>
						<table class="table table-bordered table-striped new_table">
							<tr>
								<th>SL</th>
								<th>Brand Name</th>
								<th>Model</th>
								<?php
									$current_year = date('Y');
								?>
								<th><?php echo $current_year; ?></th>
								<th><?php echo $current_year + 1; ?></th>
								<th><?php echo $current_year + 2; ?></th>
								<th><?php echo $current_year + 3; ?></th>
								<th><?php echo $current_year + 4; ?></th>
							</tr>
							<?php $i=1; foreach($laptop_based_eol as $k2=>$v2): ?>
							<tr>
								<td><?php echo $i; ?></td>
								<td><?php echo $v2->BRAND; ?></td>
								<td><?php echo $v2->MODEL; ?></td>
								<td><?php echo $v2->S1; ?></td>
								<td><?php echo $v2->S2; ?></td>
								<td><?php echo $v2->S3; ?></td>
								<td><?php echo $v2->S4; ?></td>
								<td><?php echo $v2->S5; ?></td>
							</tr>
							<?php $i++; endforeach; ?>
						</table><br /><br />
					</div>
				</div>
				
				<div style="overflow:auto">
					<div class="year_wise_total_laptop_chart">
						<div class="breadcrumb"><b>Year Wise Total Laptop. </b><a class="btn btn-danger btn-xs btn_year_wise_total_laptop_chart"> View as Table</a></div>
						<div id="yearwisetotalchartContainer" style="height: 400px; width: 100%;"></div>
					</div>
					
				<div class="year_wise_total_laptop_table">
					<div class="breadcrumb"><b>Year Wise Total Laptop.</b><a class="btn btn-danger btn-xs btn_year_wise_total_laptop_table"> View as Chart</a></div>
					<table class="table table-bordered table-striped new_table">
						<tr>
							<th>SL</th>
							<th>Brand Name</th>
							<th>Model</th>
							<?php
								$current_year = date('Y');
							?>
							<th><?php echo $current_year; ?></th>
							<th><?php echo $current_year - 1; ?></th>
							<th><?php echo $current_year - 2; ?></th>
							<th><?php echo $current_year - 3; ?></th>
							<th><?php echo $current_year - 4; ?></th>
						</tr>
						<?php $i=1; foreach($year_wise_data as $k2=>$v2): ?>
						<tr>
							<td><?php echo $i; ?></td>
							<td><?php echo $v2->BRAND; ?></td>
							<td><?php echo $v2->MODEL; ?></td>
							<td><?php echo $v2->S1; ?></td>
							<td><?php echo $v2->S2; ?></td>
							<td><?php echo $v2->S3; ?></td>
							<td><?php echo $v2->S4; ?></td>
							<td><?php echo $v2->S5; ?></td>
						</tr>
						<?php $i++; endforeach; ?>
					</table><br /><br />
				</div>
				</div>
				
			</div>
			<div class="right_section">
<?php $distribution_total = $this->db->query("SELECT * FROM TBL_STOCK WHERE STATUS = 12 ")->num_rows(); ?>
			
				<div class="distribut_laptop_chart">
					<div class="breadcrumb"><b>Distributed laptop. (Total <?php echo $distribution_total; ?> ) </b> <a class="btn btn-danger btn-xs btn_distribut_laptop_chart"> View as Table</a></div>
					<div id="distributchartContainer" style="height: 400px; width: 100%;"></div>
				</div>
				
				<div class="distribut_laptop_table">
					<div class="breadcrumb"><b>Distributed laptop. (Total <?php echo $distribution_total; ?> ) </b> <a class="btn btn-danger btn-xs btn_distribut_laptop_table"> View as Chart</a></div>
					<table class="table table-bordered table-striped new_table">
						<tr>
							<th>SL</th>
							<th>Brand Name</th>
							<th>Model</th>
							<th>Total</th>
						</tr>
						<?php $i=1; foreach($distribution_laptop as $k1=>$v1): ?>
						<tr>
							<td><?php echo $i; ?></td>
							<td><?php echo $v1->BRAND; ?></td>
							<td><?php echo $v1->MODEL; ?></td>
							<td><?php echo $v1->STOCK; ?></td>
						</tr>
						<?php $i++; endforeach; ?>
					</table><br /><br />
				</div>
<?php $available_total = $this->db->query("SELECT * FROM TBL_STOCK WHERE STATUS = 11 ")->num_rows(); ?>
				<div class="available_laptop_chart">
					<div class="breadcrumb"><b>Available laptop. (Total <?php echo $available_total; ?> )  </b><a class="btn btn-danger btn-xs btn_available_laptop_chart"> View as Table</a></div>
					<div id="availablechartContainer" style="height: 400px; width: 100%;"></div>
				</div>
				
				<div class="available_laptop_table">
					<div class="breadcrumb"><b>Available laptop. (Total <?php echo $available_total; ?> ) </b> <a class="btn btn-danger btn-xs btn_available_laptop_table"> View as Chart</a></div>
				<table class="table table-bordered table-striped new_table">
					<tr>
						<th>SL</th>
						<th>Brand Name</th>
						<th>Model</th>
						<th>Stock</th>
					</tr>
					<?php $i=1; foreach($available_laptop as $k1=>$v1): ?>
					<tr>
						<td><?php echo $i; ?></td>
						<td><?php echo $v1->BRAND; ?></td>
						<td><?php echo $v1->MODEL; ?></td>
						<td><?php echo $v1->STOCK; ?></td>
					</tr>
					<?php $i++; endforeach; ?>
				</table><br /><br />
				</div>
				
				
			</div>
			</div><!--end .page_body-->
		</div>
		
		<div id="footer_container">
			<div id="page_footer" class="text-center">
				<font color="#EF1B24"><?php echo $this->webspice->settings()->copyright_text; ?></font>
			</div>
		</div>
	</div>
	<script>
window.onload = function () {
	var chart1 = new CanvasJS.Chart("totalchartContainer",
	{
		exportFileName: "Total laptop chart",
		exportEnabled: false,
        animationEnabled: true,
		legend:{
			verticalAlign: "bottom",
			horizontalAlign: "center"
		},
		data: [
		{       
			type: "pie",
			showInLegend: false,
			toolTipContent: "{legendText}: <strong>{y}</strong>",
			indexLabel: "{label} {y} PC",
			dataPoints: [
					<?php foreach($total_laptop as $k=>$v): ?>
					{  y: <?php echo $v->STOCK; ?>, legendText: "<?php echo $v->BRAND." (".$v->MODEL.")"; ?>", label: "<?php echo $v->BRAND; ?>" },
					<?php endforeach; ?>
			]
	}
	]
	});
	chart1.render();
	
	var chart2 = new CanvasJS.Chart("availablechartContainer",
	{
		exportFileName: "Available laptop chart",
		exportEnabled: false,
        animationEnabled: true,
		legend:{
			verticalAlign: "bottom",
			horizontalAlign: "center"
		},
		data: [
		{       
			type: "pie",
			showInLegend: false,
			toolTipContent: "{legendText}: <strong>{y}</strong>",
			indexLabel: "{label} {y} PC",
			dataPoints: [
					<?php foreach($available_laptop as $k=>$v): ?>
					{  y: <?php echo $v->STOCK; ?>, legendText: "<?php echo $v->BRAND." (".$v->MODEL.")"; ?>", label: "<?php echo $v->BRAND; ?>" },
					<?php endforeach; ?>
			]
	}
	]
	});
	
	
	chart2.render();
	
	
  var chart3 = new CanvasJS.Chart("eolchartContainer",
  {
    title:{
      fontFamily: "arial black",
      fontColor: "#695A42"

    },
   animationEnabled: true,
    toolTip: {
      shared: true,
      content: function(e){
        var str = '';
        var total = 0 ;
        var str3;
        var str2 ;
        for (var i = 0; i < e.entries.length; i++){
           var  str1 = "<span style= 'color:"+e.entries[i].dataSeries.color + "'> " + e.entries[i].dataSeries.name + "</span>: <strong>"+  e.entries[i].dataPoint.y + "</strong>  PC<br/>" ; 
          total = e.entries[i].dataPoint.y + total;
          str = str.concat(str1);
        }
        str2 = "<span style = 'color:DodgerBlue; '><strong>"+ (e.entries[0].dataPoint.x).getFullYear() + "</strong></span><br/>";
        total = Math.round(total*100)/100 
        str3 = "<span style = 'color:Tomato '>Total:</span><strong> " + total + "</strong> PC<br/>";
        
        return (str2.concat(str)).concat(str3);
      }
    },
    axisY:{
      valueFormatString:"#0 PC", 
      interval: false,
      gridColor: "#B6B1A8",
      tickColor: "#B6B1A8",
      interlacedColor: "rgba(182,177,168,0.2)"

    },
    axisX: {
      interval: 1,
      intervalType: "year"
    },
    data: [
	<?php foreach($laptop_based_eol as $k=>$v): ?>    
   {  
     type: "stackedColumn",       
     showInLegend:false,
     name: "<?php echo $v->MODEL; ?>",
     dataPoints: [
     {  y: <?php echo (int)$v->S1; ?>, x: new Date(<?php echo date('Y'); ?>,0)},
     {  y: <?php echo (int)$v->S2; ?>, x: new Date(<?php echo date('Y')+1; ?>,0)},
     {  y: <?php echo (int)$v->S3; ?>, x: new Date(<?php echo date('Y')+2; ?>,0)},
     {  y: <?php echo (int)$v->S4; ?>, x: new Date(<?php echo date('Y')+3; ?>,0)},
     {  y: <?php echo (int)$v->S5; ?>, x: new Date(<?php echo date('Y')+4; ?>,0)}
     ]
   },
  <?php endforeach; ?>
   ]
 });

chart3.render();
	

	var chart4 = new CanvasJS.Chart("distributchartContainer",
	{
		exportFileName: "Chart of Distributed laptop",
		exportEnabled: false,
        animationEnabled: true,
		legend:{
			verticalAlign: "bottom",
			horizontalAlign: "center"
		},
		data: [
		{       
			type: "pie",
			showInLegend: false,
			toolTipContent: "{legendText}: <strong>{y}</strong>",
			indexLabel: "{label} {y} PC",
			dataPoints: [
					<?php foreach($distribution_laptop as $k=>$v): ?>
					{  y: <?php echo $v->STOCK; ?>, legendText: "<?php echo $v->BRAND." (".$v->MODEL.")"; ?>", label: "<?php echo $v->BRAND; ?>" },
					<?php endforeach; ?>
			]
	}
	]
	});
	chart4.render();
	
  var chart5 = new CanvasJS.Chart("yearwisetotalchartContainer",
  {
    title:{
      fontFamily: "arial black",
      fontColor: "#695A42"

    },
   animationEnabled: true,
    toolTip: {
      shared: true,
      content: function(e){
        var str = '';
        var total = 0 ;
        var str3;
        var str2 ;
        for (var i = 0; i < e.entries.length; i++){
           var  str1 = "<span style= 'color:"+e.entries[i].dataSeries.color + "'> " + e.entries[i].dataSeries.name + "</span>: <strong>"+  e.entries[i].dataPoint.y + "</strong>  PC<br/>" ; 
          total = e.entries[i].dataPoint.y + total;
          str = str.concat(str1);
        }
        str2 = "<span style = 'color:DodgerBlue; '><strong>"+ (e.entries[0].dataPoint.x).getFullYear() + "</strong></span><br/>";
        total = Math.round(total*100)/100 
        str3 = "<span style = 'color:Tomato '>Total:</span><strong> " + total + "</strong> PC<br/>";
        
        return (str2.concat(str)).concat(str3);
      }
    },
    axisY:{
      valueFormatString:"#0 PC", 
      interval: false,
      gridColor: "#B6B1A8",
      tickColor: "#B6B1A8",
      interlacedColor: "rgba(182,177,168,0.2)"

    },
    axisX: {
      interval: 1,
      intervalType: "year"
    },
    data: [
	<?php foreach($year_wise_data as $k=>$v): ?>    
   {  
     type: "stackedColumn",       
     showInLegend:false,
     name: "<?php echo $v->MODEL; ?>",
     dataPoints: [
     {  y: <?php echo (int)$v->S1; ?>, x: new Date(<?php echo date('Y'); ?>,0)},
     {  y: <?php echo (int)$v->S2; ?>, x: new Date(<?php echo date('Y')-1; ?>,0)},
     {  y: <?php echo (int)$v->S3; ?>, x: new Date(<?php echo date('Y')-2; ?>,0)},
     {  y: <?php echo (int)$v->S4; ?>, x: new Date(<?php echo date('Y')-3; ?>,0)},
     {  y: <?php echo (int)$v->S5; ?>, x: new Date(<?php echo date('Y')-4; ?>,0)}
     ]
   },
  <?php endforeach; ?>
   ]
 });

chart5.render();
	
}
	</script>
	<script>
		$(document).ready(function(){
			$('.page_caption, .page-body-title').css('color','#EF1B24');
			/*Total Laptop Chart*/
			$(".total_laptop_table").hide();
			$(".btn_total_laptop_table").click(function(){
				$(".total_laptop_table").hide(1000);
				$(".total_laptop_chart").show(1000);
			})
			
			$(".btn_total_laptop_chart").click(function(){
				$(".total_laptop_chart").hide(1000);
				$(".total_laptop_table").show(1000);
			})
			
			/*Available Laptop Chart*/
			$(".available_laptop_table").hide();
			$(".btn_available_laptop_table").click(function(){
				$(".available_laptop_table").hide(1000);
				$(".available_laptop_chart").show(1000);
			})
			
			$(".btn_available_laptop_chart").click(function(){
				$(".available_laptop_chart").hide(1000);
				$(".available_laptop_table").show(1000);
			})
			
			/*Laptop EOL Information*/
			$(".eol_laptop_table").hide();
			$(".btn_eol_laptop_table").click(function(){
				$(".eol_laptop_table").hide(1000);
				$(".eol_laptop_chart").show(1000);
			})
			
			$(".btn_eol_laptop_chart").click(function(){
				$(".eol_laptop_chart").hide(1000);
				$(".eol_laptop_table").show(1000);
			})
			
			/*Distributed Laptop Information*/
			$(".distribut_laptop_table").hide();
			$(".btn_distribut_laptop_table").click(function(){
				$(".distribut_laptop_table").hide(1000);
				$(".distribut_laptop_chart").show(1000);
			})
			
			$(".btn_distribut_laptop_chart").click(function(){
				$(".distribut_laptop_chart").hide(1000);
				$(".distribut_laptop_table").show(1000);
			})
			
			/*Laptop Year Wise Total Information*/
			$(".year_wise_total_laptop_table").hide();
			$(".btn_year_wise_total_laptop_table").click(function(){
				$(".year_wise_total_laptop_table").hide(1000);
				$(".year_wise_total_laptop_chart").show(1000);
			})
			
			$(".btn_year_wise_total_laptop_chart").click(function(){
				$(".year_wise_total_laptop_chart").hide(1000);
				$(".year_wise_total_laptop_table").show(1000);
			})
			
		});
	</script>
</body>
</html>