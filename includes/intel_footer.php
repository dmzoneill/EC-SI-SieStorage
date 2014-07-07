			
			<br />	<br />		
		</td>	
		<td width='300' style='padding-left:20px;vertical-align:top;'>
		
			<?php
			
				$employees = $common->current_user->getemployees();
			
				if( $employees->size() > 0 )
				{					
					print "<h2 class='hr' style='border-bottom:1px dashed #99f;text-align:left'>Employees</h2>";
					
					for( $t = 0; $t < $employees->size(); $t++ )
					{
						$emp = $employees->get( $t );						
						print "<a href='users.php?requested_user=" . $emp->samaccountname . "'>" . $emp->cn . "<br/>";
					}			
				}
				
			?>
		
		</td>
	</tr>	
</table>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-1014155-4']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</body>
</html>
