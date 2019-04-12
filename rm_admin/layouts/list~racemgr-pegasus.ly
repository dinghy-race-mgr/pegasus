@version=2
@align=center
@vspacing=0
@hspacing=0
beginblock screenbound top


    begincontainer horizontal  style=empty toplinks
		
    endcontainer
	begincontainer horizontal bmargin=0 style=undermenu hmenu
		brick hmenu
 		brick right search
		brick right search_buttons
		brick right search_saving_buttons
	endcontainer
	
endblock 

beginblock width=100% center    
	begincontainer horizontal height=50px style=2 width=80% recordcontrols
     brick masterinfo
     brick right recordcontrols_new
	   brick left recordcontrol_hidden
	   brick left toplinks_hidden
		 brick right languages
  endcontainer 
    
  begincontainer horizontal style=2 width=100% message
       brick center align=center width=100% filename=message_bare.htm message
  endcontainer

    begincontainer horizontal height=36px style=2 width=100% pagination
		brick left details_found
        brick center pagination
		brick right page_of
		brick right recsperpage
    endcontainer

    begincontainer grid delimx=10 delimy=10 width=100% style=grid grid
        brick grid 
    endcontainer 
    begincontainer horizontal height=36px style=2 width=100% pagination_bottom
		brick left details_found
        brick center pagination
		brick right page_of
		brick right recsperpage
    endcontainer
endblock
