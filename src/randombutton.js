//@Project: Nick Higgins' Web Site 
//@Author:  Nicholas Higgins
//@Contact: spybob888@aol.com
//@Created: 10:33 AM Sunday, January 31, 2016
//@Modified: 11:25 AM Thursday, April 09, 2020


function randInt(size) {
    return Math.ceil(Math.random()*size);
  }
      function getSite(jNum)
      {
          var mtButtons = new Array();
            mtButtons[1] = "<img src=\"aimlink.gif\">";
            mtButtons[2] = "<img src=\"abinow.gif\">";
            mtButtons[3] = "<img src=\"acrobat.gif\">";
            mtButtons[4] = "<img src=\"any_browser.gif\">";
            mtButtons[5] = "<img src=\"browser1.gif\">";
            mtButtons[6] = "<img src=\"browser7.gif\">";
            mtButtons[7] = "<img src=\"built_with_microsoft_notepad.gif\">";
            mtButtons[8] = "<img src=\"c64ik.gif\">";
            mtButtons[9] = "<img src=\"comdex4.gif\">";
            mtButtons[10] = "<img src=\"ddialnowanim.gif\">";
            mtButtons[11] = "<img src=\"dilbert.gif\">";
            mtButtons[12] = "<img src=\"divx_logo2.gif\">";
            mtButtons[13] = "<img src=\"edpadico.gif\">";
            mtButtons[14] = "<img src=\"EmacsNow.gif\">";
            mtButtons[15] = "<img src=\"email-icon.gif\">";
            mtButtons[16] = "<img src=\"ftplogo.gif\">";
            mtButtons[17] = "<img src=\"geocities_silicon_valley01.gif\">";
            mtButtons[18] = "<img src=\"get_java.gif\">";
            mtButtons[19] = "<img src=\"getacomp.jpg\">";
            mtButtons[20] = "<img src=\"getbsod.gif\">";
            mtButtons[21] = "<img src=\"getexcelviewer.gif\">";
            mtButtons[22] = "<img src=\"getflash.gif\">";
            mtButtons[23] = "<img src=\"getmozilla2.gif\">";
            mtButtons[24] = "<img src=\"hicolor.gif\">";
            mtButtons[25] = "<img src=\"javanow.gif\">";
            mtButtons[26] = "<img src=\"kendall.gif\">";
            mtButtons[27] = "<img src=\"kicq-now.gif\">";
            mtButtons[28] = "<img src=\"linux_now.gif\">";
            mtButtons[29] = "<img src=\"midi_files_now.gif\">";
            mtButtons[30] = "<img src=\"mt32now.gif\">";
            mtButtons[31] = "<img src=\"n-as.gif\">";
            mtButtons[32] = "<img src=\"netcom.gif\">";
            mtButtons[33] = "<img src=\"netscape.gif\">";
            mtButtons[34] = "<img src=\"notepad2.gif\">";
            mtButtons[35] = "<img src=\"nxftp.gif\">";
            mtButtons[36] = "<img src=\"pedit.gif\">";
            mtButtons[37] = "<img src=\"pngnow.png\">";
            mtButtons[38] = "<img src=\"pptani.gif\">";
            mtButtons[39] = "<img src=\"sun.gif\">";
            mtButtons[40] = "<img src=\"telnet.gif\">";
            mtButtons[41] = "<img src=\"valid401.png\">";
            mtButtons[42] = "<img src=\"vcss.gif\">";
            mtButtons[43] = "<img src=\"vi_now.gif\">";
            mtButtons[44] = "<img src=\"vilogo.gif\">";
            mtButtons[45] = "<img src=\"winamp-miniban.gif\">";
            mtButtons[46] = "<img src=\"website.gif\">";
            mtButtons[47] = "<img src=\"winzip.gif\">";
            mtButtons[48] = "<img src=\"zxcert.png\">";
          
          var usedSites = new Array();
          var buttons = new Array();
          let num = 0;
          let siteidx;

          while (num < 4) {
            siteidx = randint(48);
            if !(usedSites.includes(siteidx)) {
              usedSites.push(siteidx);
              buttons.push(mtButtons[num]);
              num++;
            } else {
              
            }
          }
          return mtSites[jNum];
      }
      document.write(getSite(randInt(16)));
  