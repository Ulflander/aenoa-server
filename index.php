<?php

	if ( headers_sent () == false )
	{
		header('Status: 403 Forbidden', false, 403);
	}

	die ( '<h2>Forbidden</h2>' ) ;
?>