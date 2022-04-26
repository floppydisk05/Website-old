function openWindow(url){
	Window = window.open(url, 'Win', 'height=440,width=600,toolbar=no,resizable=yes,scrollbars=yes');
    Window.focus();
}

function openEula(url){
	Window = window.open(url, 'Win', 'height=440,width=600,toolbar=no,resizable=no,scrollbars=yes');
    Window.focus();
}

function openExample(url){
	Window = window.open(url, 'Win', 'height=440,width=600,toolbar=yes,resizable=yes,scrollbars=yes');
    Window.focus();
}





var rollData = new Array(); // Main Data Container

 //RollOver Array: Contains Off and On Data- 1 : 0
rollData[0] = new roData('./img/nav_off.gif','./img/nav_on.gif');
rollData[1] = new roData('img/nav_off.gif','img/nav_on.gif');

   // Data Object for Array
function roData(imgoff,imgon){
   this.imgOff = new Image(); this.imgOff.src = imgoff;
   this.imgOn = new Image();  this.imgOn.src = imgon;
}// end

  // Actual RollOver Function
  // Who: Image In Page, Ary: Array Element, State: On/Off (1,0)
function roll(who,ary,state){ // Used in Roll Overs.
  if (state == 1){
     document.images[who].src = rollData[ary].imgOn.src;
  }else{
     document.images[who].src = rollData[ary].imgOff.src;
  }// end if 
}// end Swop

