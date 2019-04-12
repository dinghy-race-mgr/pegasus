@version=2
beginblock top
	begincontainer vertical style=empty width=400 align=center all
	begincontainer vertical style=1 width=400 align=center add
		brick color2 viewheader
		brick message
		brick multistep_nav_view
	begincontainer vertical style=fields fields
		brick viewfields2_atop
		brick color2 viewbuttons
	endcontainer
	endcontainer
	begincontainer vertical style=empty width=100% details
		brick viewdetails
	endcontainer
	endcontainer
endblock
