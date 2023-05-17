<html>
<head>
  <title>Please wait</title>
</head>
<body style="border:5px solid lightblue;margin:0;padding:5px;font-family:arial;" onclick="olClose();">
  <h3>Please wait..</h3>
  <p>Preparing your file may take a few seconds.</p>
  <p>This message will close itself, or you can click or tap it.
</body>
<script>
  function olClose() {
     var ol = parent.document.getElementById("overlay");
     ol.style.display = 'none';
     setTimeout('parent.document.getElementById("ifrm").src="/includes/empty.htm"', 200);
  }

</script>
</html>
