<?php
$loc=((isset($_GET['loc']))?$_GET['loc']:'/matt/1');
//print('location.href="'.$loc.'";');
?>

<html>
<head>
  <title>Navigation Notice</title>
  <style>
.gobackbutton{
  text-align:center;
  font-size:18px;
  width:140px;
  height:1.7em;
  border-radius:8px;
  border:1px solid #aaa;
  background-color:#f0f0f0;
  color:black;
}

  </style>
</head>
<body style="border:5px solid lightblue;margin:0;padding:5px;font-family:arial;">
  <h3 style="text-align:center">Please note</h3>
  <p>While you can use the REV Search function to navigate to a place in the Bible, it is far more efficient to use the Navigation bar at the top of the screen.</p>
  <p>To learn how to effectively navigate the REV, please see the <a onclick="parent.location.href='/info/3';" style="color:blue;cursor:pointer;">Help page</a> for more information.</p>
  <p style="text-align:center;"><input type="button" class="gobackbutton" value="Continue" onclick="olClose();" /></p>
</body>
<script>
  function olClose() {
     //var ol = parent.document.getElementById("overlay");
     //ol.style.display = 'none';
     //setTimeout('parent.document.getElementById("ifrm").src="/includes/empty.htm"', 200);
     parent.location.href="<?=$loc?>";
  }

</script>
</html>
