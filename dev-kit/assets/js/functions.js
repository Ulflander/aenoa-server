
var indicatorInterval = 800 ;
var interval ;
var interval2 ;
var timeBeforeRedirect = 0 ;
var redirectMessage = '' ;
var redirectURL = '' ;

function switchIndicatorState ()
{
	if ( document.getElementById("indicator").className.indexOf("alternate") > -1 )
	{
		document.getElementById("indicator").className = document.getElementById("indicator").className.split("alternate").join("") ;
	} else {
		document.getElementById("indicator").className = document.getElementById("indicator").className + " alternate" ;
	}
}

function hideIndicator ()
{
	if ( interval2 == undefined )
	{
		document.getElementById("indicator").style.display="none" ;
		
		if ( document.getElementById("menu") )
		{
			document.getElementById("menu").style.display="block" ;
		}
	
		focusOnLastSearchForm () ;
	}
	
	window.clearInterval ( interval ) ;
}


function redirect ( message , URL , delay )
{
	redirectMessage = message ;
	redirectURL = URL ;
	
	if ( delay == undefined )
	{
		delay = 5000 ;
	}
	if ( document.getElementById("indicator").className.indexOf("alternate") > -1 )
	{
		document.getElementById("indicator").className = document.getElementById("indicator").className.split("alternate").join("") ;
	}
	document.getElementById("menu").style.display="block" ;
	document.getElementById("indicator").style.display="block" ;
	
	timeBeforeRedirect = delay / 1000;
	setRedirectMessage () ;
	interval2 = window.setInterval("indicatorBeforeRedirect()", 1000); 
}

function setRedirectMessage ()
{
	document.getElementById("indicator").innerHTML = redirectMessage + " <strong>" + timeBeforeRedirect + "s</strong> - " 
		+ "<a href='"+redirectURL+"'>Click here to be redirected now</a>";
}
function indicatorBeforeRedirect ()
{
	timeBeforeRedirect -= 1 ;
	
	if ( timeBeforeRedirect == 0 )
	{
		window.clearInterval ( interval2 ) ;
		self.location.href = redirectURL ;
		return;
	}
	setRedirectMessage () ;
}

function focusOnLastSearchForm ()
{
	var forms = document.getElementsByName ( 'query' ) ;
	var last = forms[forms.length-1] ;
	last.focus () ;
}

interval = window.setInterval("switchIndicatorState()", indicatorInterval); 
