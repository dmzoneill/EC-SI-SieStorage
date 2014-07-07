var loadinganim = 0;

function goto( url )
{
	$( 'maincontent' ).html( "<br><br><br><br><br><center><img src='images/ajax-loader.gif'></center>" );
	document.location.href = url;
}


var rotation = function ()
{                  
    if( loadinganim == 1 )
    {
        loadinganim = 0;
        return;
    }
    
    $( "#intelloading" ).rotate(
    {                           
        angle: 0,               
        animateTo: 360,         
        callback: rotation      
    });
    
    $( "#intelloadingCon" ).animate(
    {                               
        width: 120,                 
        height: 125,                
        top:12,                     
        left:16                     
    } , 300 ).animate(              
    {                               
        width: 162,                 
        height: 150,                
        top:0,                      
        left:0                      
    } , 300 );                      
}               
