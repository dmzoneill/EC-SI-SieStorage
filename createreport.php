<?php

if( isset( $_POST[ 'formhidden' ] ) )
{
    
    include( "includes/csvrow.class.php" );
    include( "includes/ldapuser.class.php" );
    include( "includes/ldapgroup.class.php" );
    include( "includes/ldap.class.php" );
    include( "includes/common.class.php" );

    $ldap = new Ldap();
    $common = new Common( $ldap );
	
    $matches = array();
    $filters = array();
    $orders  = array();
    
    foreach( $_POST as $postkey => $postvalue )
    {
        if( preg_match( "/(wherefield)([0-9]+)/" , $postkey , $matches ) )
        {
            $num = $matches[ 2 ];
            
            $wherefield = "wherefield" . $num;
            $wherefield = $_POST[ $wherefield ];
            
            $whereoperator = "whereoperator" . $num;
            $whereoperator = $_POST[ $whereoperator ];
            
            $whereval = "whereval" . $num;
            $whereval = $_POST[ $whereval ];
            
            $whereopt = "whereopt" . $num;
            $whereopt = isset( $_POST[ $whereopt ] ) ? $_POST[ $whereopt ] : false;
           
            if( count( $filters ) > 0 && $wherefield != "" )
            {
                $filters[] = array( "and" );
            }
            
            if( $wherefield != "" )
            {
                $filters[] = array( $wherefield , $whereoperator , $whereval );
            
                if( $whereopt == "and" )
                {
                    $filters[] = array( "and" );
                }
            }
        }
        else if( preg_match( "/(sortfield)([0-9]+)/" , $postkey , $matches ) )
        {
            $num = $matches[ 2 ];

            $sortfield = "sortfield" . $num;
            $sortfield = $_POST[ $sortfield ];

            $sortorder = "sortfield" . $num;
            $sortorder = $_POST[ $sortorder ];

            $orders[] = array( $sortfield , $sortorder );
        }
        else if( preg_match( "/report_bus/" , $postkey , $matches ) )
        {
            if( count( $filters ) > 0 )
            {
                $filters[] = array( "and" );
            }
            
            foreach( $postvalue as $val )
            {
                if( $val != "" ) 
                {
                    $filters[] = array( "BusinessGroup" , "=" , $val );
                }
            }
        }
        else if( preg_match( "/report_ps/" , $postkey , $matches ) )
        {
            if( count( $filters ) > 0 )
            {
                $filters[] = array( "and" );
            }
            
            foreach( $postvalue as $val )
            {
                if( $val != "" ) 
                {
                    $filters[] = array( "Project" , "=" , $val );
                }
            }
        }
    }
    
    $fields = shell_exec( "cat stoddump/" . $common->site . "/areas.csv | head -n 1" );
    $fields = explode( "," , $fields );

    for( $i = 0; $i < count( $fields ); $i++ )
    {
        $fields[ $i ] = trim( $fields[ $i ] );
    }

    $arr = file( "stoddump/" . $common->site . "/areas.csv" );
    $lines = array();
    
    for( $t = 1; $t < count( $arr ); $t++ )
    {
        $parts = explode( "," , $arr[ $t ] );

        if( count( $parts ) > 0 )
        {
            $lines[] = $parts;
        }
    }

    foreach( $lines as $line )
    {
        $fior = array();
        $fiand = array();

        foreach( $filters as $filter )
        {
            if( count( $filter ) == 1 )
            {
                if( in_array( 1 , $fior ) )
                {
                    $fiand[] = 1;
                }
                else
                {
                    $fiand[] = 0;
                }

                $fior = array();
            }
            else
            {
                $index = array_search( $filter[ 0 ] , $fields );
            
                switch( $filter[ 1 ] )
                {
                    case "=":
                        
                        $fior[] = ( $line[ $index ] == $filter[ 2 ] ) ? 1 : 0;
                        
                    break;
                
                    case "!=":

                        $fior[] = ( $line[ $index ] != $filter[ 2 ] ) ? 1 : 0;
                        
                    break;

                    case ">":

                        $fior[] = ( $line[ $index ] > $filter[ 2 ] ) ? 1 : 0;
                    
                    break;

                    case "<":

                        $fior[] = ( $line[ $index ] < $filter[ 2 ] ) ? 1 : 0;
                    
                    break;

                    case "~":

                        $fior[] = ( preg_match( "/.*?(" . $filter[ 2 ] . ").*?/" , $line[ $index ] , $match ) ) ? 1 : 0;
                    
                    break;
                }
            }
        }

        if( in_array( 1 , $fior ) )
        {
            $fiand[] = 1;
        }
        else
        {
            $fiand[] = 0;
        }

        if( !in_array( 0 , $fiand ) )
        {
            $matches[] = $line;
        }        
    }
   
    print "<table id='reporttable'>";
    print "<thead><tr>";
    
    foreach( $_POST[ 'report_fields' ] as $col )
    {
        if( $col == "" ) continue;
        
        print "<th>" . $col . "</th>";
    }

    print "</tr></thead><tbody>";

    if( count( $orders ) > 1 )
    {
        $index = array_search( $orders[ 0 ][ 0 ] , $_POST[ 'report_fields' ] );

        function cmp( $a , $b )
        {
            if( $orders[ 0 ][ 1 ] == "ASC" )
            {
                if( $a > $b ) 
                {
                    return 0;
                }
                
                return 1;
            }
            
            if( $a < $b )
            {
                return 0;
            }
            
            return 1;
        }
        
        usort( $matches[ $index ] , "cmp" );
    }
    else
    {
        $index1 = array_search( $orders[ 0 ][ 0 ] , $_POST[ 'report_fields' ] );
        $index2 = array_search( $orders[ 1 ][ 0 ] , $_POST[ 'report_fields' ] );
        $sort1 = ( $orders[ 0 ][ 1 ] == "ASC" ) ? SORT_ASC : SORT_DESC;
        $sort2 = ( $orders[ 1 ][ 1 ] == "ASC" ) ? SORT_ASC : SORT_DESC;
        
        array_multisort( $matches[ $index1 ] , $sort1 , SORT_REGULAR, $matches[ $index2 ] , $sort2 , SORT_REGULAR );
    }
    
    foreach( $matches as $match )
    {
        print "<tr>";
        
        foreach( $_POST[ 'report_fields' ] as $col )
        {
            if( $col == "" ) continue;

            $index = array_search( $col , $fields );
            
            print "<td>" . $match[ $index ] . "</td>";
        }

        print "</tr>";
    }

    print "</tbody></table>";
    
    exit;
    
}
else
{
    include( "includes/intel_header.php" );
    
    $fields = shell_exec( "cat stoddump/" . $common->site . "/areas.csv | head -n 1" );
    $fields = explode( "," , $fields );

    for( $i = 0; $i < count( $fields ); $i++ )
    {
        $fields[ $i ] = trim( $fields[ $i ] );
    }

    $arr = file( "stoddump/" . $common->site . "/areas.csv" );
    $fieldvals = array();
        
    for( $i = 0; $i < count( $fields ); $i++ )
    {
        $fieldvals[ $fields[ $i ] ] = array();
    }

    for( $t = 1; $t < count( $arr ); $t++ )
    {
        $line = explode( "," , $arr[ $t ] );

        for( $i = 0; $i < count( $fields ); $i++ )
        {
            $fieldvals[ $fields[ $i ] ][] = "\"" . trim( $line[ $i ] ) . "\"";
        }    
    }

    $vls = "\nvar fieldvals = {\n";
       
    foreach( $fieldvals as $key => $val )
    {
        $vls .= "\t'$key' : [" . implode( "," , array_unique( $val ) ) . "],\n";
    }

    $vls = substr( $vls , 0 , -2 );
        
    $vls .= "}\n";

    $keys = array();
    
    foreach( $common->businessgroups as $gr )
    {
        $groupProjects = array();
        
        foreach( $common->areas as $area )
        {
            if( $gr == $area->BusinessGroup && !in_array( "'" . $area->Project . "'" , $groupProjects ) )
            {
                $groupProjects[] = "'" . $area->Project . "'";
            }
        }
        
        $values = implode( "," , $groupProjects );
        $keys[] = "'$gr' : [ $values ]";
    }
                
    $fieldsOptionsHtml = "";

    foreach( $fields as $field )
    {
        $fieldsOptionsHtml .= "<option value='" . $field . "'>" . $field . "</option>\n";
    }

    $whererow = "<tr id='wheretrNUM'>                                                                                                                          
                    <td valign='top'>                                                                                                                       
                        <select name='wherefieldNUM' id='wherefieldNUM' style='width:200px;margin:5px' onchange='populateFieldVals(this)'>                                                                          
                            <option value=''>Select</option>                                                                                                
                            $fieldsOptionsHtml                                                                                              
                        </select>                                                                                                                           
                    </td>                                                                                                                                   
                    <td>                                                                                                                                    
                        <select name='whereoperatorNUM' id='whereoperatorNUM' style='width:200px;margin:5px'>                                                                       
                            <option value=''>Select</option>                                                                                                
                            <option value='='>Equal</option>                                                                                                
                            <option value='!='>Not equal</option>                                                                                           
                            <option value='>'>Greater than</option>                                                                                         
                            <option value='<'>Less than</option>                                                                                            
                            <option value='~'>Like</option>                                                                                                 
                        </select>                                                                                                                           
                    </td>                                                                                                                                   
                    <td>                                                                                                                                    
                        <select name='wherevalNUM' id='wherevalNUM' style='width:200px;margin:5px'>                                                                            
                            <option value=''>Select</option>                                                                                                
                        </select>                                                                                                                           
                    </td>                                                                                                                                   
                    <td>                                                                                                                                    
                        <select name='whereoptNUM' id='whereoptNUM' disabled='disabled' style='margin:5px'>
                             <option value='and'>And</option>                                                                                                
                             <option value='or'>Or</option>                                                                                                  
                        </select>
                    </td>
                </tr>";

        $whererow = preg_replace( "/>\s+?</sm" , "><" , $whererow );

        $orderrow = "<tr id='ordertrNUM'>
                        <td>
                            <select name='sortfieldNUM' id='sortfieldNUM' style='width:200px;margin:5px'>
                                <option value=''>Select</option>
                                $fieldsOptionsHtml
                            </select>
                        </td>
                        <td colspan='3'>
                            <select name='sortorderNUM' id='sortorderNUM' style='width:200px;margin:5px'>
                                <option value='ASC'>Ascending</option>
                                <option value='DESC'>Descending</option>
                            </select>
                        </td>
                    </tr>";

        $orderrow = preg_replace( "/>\s+?</sm" , "><" , $orderrow );
        
?>
       
    <script language='javascript'>
                    
        var wheres = 1;
        var orders = 1;
        var whererow = "<?php print $whererow; ?>";
        var orderrow = "<?php print $orderrow; ?>";
                        
        <?php print $vls; ?>
    
        function addRow( x )
        {
            if( x == 0 )
            {
                var oldrow = wheres;
                var newrow = wheres + 1;
                var rowhtml = $( whererow.replace( /NUM/g , newrow ) );
                $( "#wheretr" + oldrow ).after( rowhtml );
                $( "#whereopt" + oldrow ).removeAttr( 'disabled' ); 
                wheres += 1;
            }
            else
            {
                if( orders == 2 ) return;

                var oldrow = orders;
                var newrow = orders + 1;
                var rowhtml = $( orderrow.replace( /NUM/g , newrow ) );
                $( "#ordertr" + oldrow ).after( rowhtml );
                orders += 1;
            }
        }

        function delRow( x )
        {
            if( x == 0 )
            {
                if( wheres == 1 ) return;

                var prevrow = wheres - 1;
                $( "#wheretr" + wheres ).remove();
                $( "#whereopt" + prevrow ).attr( 'disabled' , true );
                wheres -= 1;
            }
            else
            {
                if( orders == 1 ) return;

                var prevrow = orders - 1;
                $( "#ordertr" + orders ).remove();
                orders -= 1;                
            }
        }

        
            
        function populateProjects()
        {
            var grProj = { <?php print implode( ",\n" , $keys ); ?> }; 

            $( "#report_ps" ).find( "option" ).remove().end().append( "<option value=''>Select</option>" ).val( "" );
        
            var optionsSecond = new Array();
            
            $( "#report_bus option:selected" ).each( function() 
            {
                var selecteditem = $( this ).val();
                
                $.each( grProj , function( key , value ) 
                {
                    if( selecteditem == key )
                    {
                        $.each( value , function( index , val )
                        {
                            if( $.inArray( val , optionsSecond ) == -1 )
                            {
                                optionsSecond.push( val );
                            }
                        });
                    }
                });
            });

            optionsSecond.sort();
            
            $.each( optionsSecond , function( index , val )
            {
                $( "#report_ps" ).append( "<option value='" + val + "'>" + val + "</option>" ).val( val );
            });

            if( optionsSecond.length == 0 )
            {
                $( "#report_ps" ).attr( 'disabled' , true );
            }
            else
            {
                $( "#report_ps" ).removeAttr( 'disabled' );
            }
        }


        function populateFieldVals( source )
        {
            var fieldid = $( source ).attr( 'id' );
            var fid = fieldid.match( /[0-9]+/ );
            var fval = $( source ).val();
            var fwval = $( "#whereval" + fid );
            var fpval = fieldvals[ fval ];            
            
            $( fwval ).find( "option" ).remove().end().append( "<option value=''>Select</option>" ).val( "" );
            
            $.each( fpval , function( index , val )
            {
                $( fwval ).append( "<option value='" + val + "'>" + val + "</option>" ).val( val );
            });
            
            $( fwval ).val(''); 
        }


        function getreport()
        {
            rotation();
            
            $.ajax(
            {
                type: 'POST', 
                url: 'createreport.php', 
                data: $('#reportform').serialize(), 
                success: function( response ) 
                {
                    loadinganim = 1;
                    $( '#reportresult' ).html( response );
                    $( '#reporttable' ).dataTable(
                    {
                        "bPaginate": false
                    });
                }
            });
        }
        
    </script>
    <h2>Report Creator</h2>	
	<form id='reportform'>
        <input type='hidden' name='formhidden' value='1'>	
        <table width='750'>
            <tr>
                <td valign='top'> 
                    <h4>Business Group</h4>
                    <select name='report_bus[]' id='report_bus' size='8' style='width:200px;margin:5px' multiple='multiple' onChange='populateProjects()'>
                        <option value=''>Select</option>
                        <?php
                            foreach( $common->businessgroups as $bu )
                            {
                                print "<option value='" . $bu . "'>" . $bu . "</option>\n";
                            }
                        ?>
                    </select>
                </td>
                <td valign='top'>
                    <h4>Project</h4>
                    <select name='report_ps[]' id='report_ps' size='8' style='width:200px;margin:5px' multiple='multiple'>
                        <option value=''>Select</option>
                    </select>
                </td>
                <td valign='top' colspan='2'>
                    <h4>Select</h4>
                    <select name='report_fields[]' id='report_fields' size='8' style='width:200px;margin:5px' multiple='multiple'>
                        <option value=''>Select</option>
                        <?php print $fieldsOptionsHtml; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan='4'>
                    <h4>Where</h4>
                </td>
            </tr>
            <tr id='wheretr1'>
                <td valign='top'>
                    <select name='wherefield1' id='wherefield1' style='width:200px;margin:5px' onchange="populateFieldVals(this)">
                        <option value=''>Select</option>
                        <?php print $fieldsOptionsHtml; ?>
                    </select>
                </td>
                <td>
                    <select name='whereoperator1' id='whereoperator1' style='width:200px;margin:5px'>
                        <option value=''>Select</option>
                        <option value='='>Equal</option>
                        <option value='!='>Not equal</option>
                        <option value='>'>Greater than</option>
                        <option value='<'>Less than</option>
                        <option value='~'>Like</option>
                    </select>
                </td>
                <td>
                    <select name='whereval1' id='whereval1' style='width:200px;margin:5px'>
                        <option value=''>Select</option>
                    </select>
                </td>
                <td>
                    <select name='whereopt1' id='whereopt1' disabled='disabled' style='margin:5px'>
                        <option value='and'>And</option>
                        <option value='or'>Or</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan='4'>
                    <input style='width:30px;height:30px;margin:5px' type='button' value='+' onclick='addRow(0)'/>
                    <input style='width:30px;height:30px;margin:5px' type='button' value='-' onclick='delRow(0)'/>
                </td>
            </tr>
            <!---
            <tr>
                <td colspan='4'>
                    <h4>Order By</h4>
                </td>
            </td>
            <tr id='ordertr1'>
                <td>
                    <select name='sortfield1' id='sortfield1' style='width:200px;margin:5px'>
                        <option value=''>Select</option>
                        <?php print $fieldsOptionsHtml; ?>
                    </select>
                </td>
                <td colspan='3'>
                    <select name='sortorder1' id='sortorder1' style='width:200px;margin:5px'>
                        <option value='ASC'>Ascending</option>
                        <option value='DESC'>Descending</option>
                    </select>
                    <br>
                </td>
            </tr>
            <tr>
                <td colspan='4'>
                    <input style='width:30px;height:30px;margin:5px' type='button' value='+' onclick='addRow(1)'/>
                    <input style='width:30px;height:30px;margin:5px' type='button' value='-' onclick='delRow(1)'/>
                </td>
            </tr>
            // --->
            <tr>
                <td colspan='4'>
                    <h4>Generate Report </h4>
                </td>
            </tr>
            <tr>
                <td colspan='4'>
					<input type='button' style='margin:5px' onclick='getreport()' value='Create Report'/>
				</td>
			</tr>
		</table>
	
	</form>
	
    <br>
    <h2>Report</h2>
    <br>
    <div id='reportresult' style='width:100%'></div>
	
	<?php
}

include( "includes/intel_footer.php" );
