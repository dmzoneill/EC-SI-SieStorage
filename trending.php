<?php

include( "includes/intel_header.php" );
?>
<h2>Trending</h2>

<table width='500'>
<tr>
<td>
<h3>Begin Date</h3>
</td>
<td>
<h3>End Date</h3>
</td>
</tr>
<tr>
<td valign='top'>
<div id="startdatepicker"></div>
</td>
<td valign='top'>
<div id="enddatepicker"></div>
</td>
</tr>
</table>

<pre>

<?php print_r( $common->trendingfiles ); ?>

</pre>

<script type='text/javascript'>

	$(document).ready( function() 
	{
		$( "#startdatepicker" ).datepicker();
		$( "#enddatepicker" ).datepicker();
	});

</script>

<?php

include( "includes/intel_footer.php" );
