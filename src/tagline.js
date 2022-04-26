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
          var mtSites = new Array();
            mtSites[1] = "One light does out, they all go out!";
            mtSites[2] = "Mariah Carey should be banned";
            mtSites[3] = "Christmas... the most expensive time of the year!";
            mtSites[4] = "Santa's lucky, he only visits people once a year";
            mtSites[5] = "Christmas is the unsucc";
            mtSites[6] = "It's Christmas at ground zero, there's music in the air...";

          return mtSites[jNum];
      }
      document.write(getSite(randInt(6)));
  