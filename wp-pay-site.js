
 var old_content;
 
 function togglediricon(id) {
  var element = document.getElementById(id);
  element.src = element.src.match(/folder_minus_grey\.gif$/i) ? element.src.replace(/folder_minus_grey\.gif$/i, 'folder_plus_grey.gif') : element.src.replace(/folder_plus_grey\.gif$/i, 'folder_minus_grey.gif');
 }

 function wpps_toggle_folder_options(checked, has_folder_value){
  if(checked){
   document.getElementById('member-folder-options').style.display = 'block';
   document.getElementById('wpps-content-type').style.display = 'block';
  }else{
   document.getElementById('member-folder-options').style.display = 'none';
   document.getElementById('wpps-content-type').style.display = 'none';
   if(has_folder_value){
    document.getElementById('wpps-startup-folder-message').style.display = 'none';
    document.getElementById('wpps_update_folder').value = '';
   }
  }
 }

 function showfoldercontent(s, dir, sid) {
  var divtag = document.getElementById(s);
    
  if (divtag.style.display == 'none') {
   var xmlhttp;
   if(window.XMLHttpRequest){ // IE7+, Firefox, Chrome, Opera, Safari
    xmlhttp = new XMLHttpRequest();
   }else if (window.ActiveXObject){ // IE6, IE5
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
   }
   xmlhttp.onreadystatechange=function(){
    if(xmlhttp.readyState == 4){
     divtag.style.display = 'block';
     divtag.innerHTML     = xmlhttp.responseText;
    }
   }
   xmlhttp.open('GET', '../wp-content/plugins/wp-pay-site/wp-pay-site.php?d='+dir+'&s='+sid, true);
   xmlhttp.send(null);
  }else {
   divtag.innerHTML = '';
   divtag.style.display = 'none';
  }
 }

 function wpps_select_dir(dir){
  var el = document.getElementById('member-folder-options');
  old_content = el.innerHTML;
  el.innerHTML = '<strong>Processing folder:</strong> ' + dir + '<p align="center"><img src="../wp-content/plugins/wp-pay-site/images/loading.gif" /><br />Scanning</p>';
  var xmlhttp;
  if(window.XMLHttpRequest){ // IE7+, Firefox, Chrome, Opera, Safari
   xmlhttp = new XMLHttpRequest();
  }else if (window.ActiveXObject){ // IE6, IE5
   xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange=function(){
   if(xmlhttp.readyState == 4){
    scan_result = '<strong>Processing folder:</strong> ' + dir + '<br /><br />';
    if(parseInt(xmlhttp.responseText) > 0){
     scan_result += xmlhttp.responseText + ' images found.<br /><br /><br /><a class="button" href="#" onclick="wpps_back(); return false;">&laquo; Back</a> <a class="button" href="#" onclick="wpps_crop_thumbs(\'' + dir + '\'); return false;">Crop Thumbnails</a> or <a class="button" href="#" onclick="wpps_save(\'' + dir + '\'); return false;">Skip and Save</a><br /><br />';
    }else{
     scan_result += '<font color="red">No images found in this folder.</font><br /><br /><br /><a class="button" href="#" onclick="wpps_back(); return false;">&laquo; Back</a><br /><br />';
    }
    el.innerHTML = scan_result;
   }
  }
  xmlhttp.open('GET', '../wp-content/plugins/wp-pay-site/wp-pay-site.php?scan_dir=' + dir, true);
  xmlhttp.send(null);
 }

 function wpps_crop_thumbs(dir){
  var el = document.getElementById('member-folder-options');
  el.innerHTML = '<strong>Processing folder:</strong> ' + dir + '<p align="center"><img src="../wp-content/plugins/wp-pay-site/images/loading.gif" /><br />Cropping</p>';
  var xmlhttp;
  if(window.XMLHttpRequest){ // IE7+, Firefox, Chrome, Opera, Safari
   xmlhttp = new XMLHttpRequest();
  }else if (window.ActiveXObject){ // IE6, IE5
   xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange=function(){
   if(xmlhttp.readyState == 4){
    scan_result = '<strong>Processing folder:</strong> ' + dir + '<br /><br />';
    if(parseInt(xmlhttp.responseText) > 0){
     scan_result += xmlhttp.responseText + ' images cropped.<br /><br /><br /><a class="button" href="#" onclick="wpps_back(); return false;">&laquo; Back</a> <a class="button" href="#" onclick="wpps_save(\'' + dir + '\'); return false;">Save</a><br /><br />';
    }else{
     scan_result += '<font color="red">No images cropped.</font><br /><br /><br /><a class="button" href="#" onclick="wpps_back(); return false;">&laquo; Back</a><br /><br />';
    }
    el.innerHTML = scan_result;
   }
  }
  xmlhttp.open('GET', '../wp-content/plugins/wp-pay-site/wp-pay-site.php?crop_dir=' + dir, true);
  xmlhttp.send(null);
 }

 function wpps_save(dir){
  var el = document.getElementById('member-folder-options');
  document.getElementById('wpps_update_folder').value = dir;
  el.innerHTML = '<strong>Current Update folder:</strong> ' + dir + ' - <a href="#" onclick="wpps_back(); return false;">change</a>';
 }

 function wpps_back(){
  var el = document.getElementById('member-folder-options');
  el.innerHTML = old_content;
 }
