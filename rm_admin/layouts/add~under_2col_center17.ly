@version=2
beginblock top 
	begincontainer vertical style=empty align=center width=400  all
		begincontainer vertical style=1 align=center width=400 add
			brick color2 addheader
			brick color1 message
			brick color1 multistep_nav_add
			begincontainer vertical color=1 style=fields fields
				brick addfields2_atop
				brick color2 addbuttons
			endcontainer 
		endcontainer 
		begincontainer vertical style=empty width=100% details
			brick adddetails
		endcontainer
	endcontainer
endblock
