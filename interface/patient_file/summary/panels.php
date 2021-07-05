<?php	
/**
 * Panels
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Wejdan Bagais <w.bagais@gmail.com>
 * @copyright Copyright (c) 2020 Wejdan Bagais <w.bagais@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/


require_once("../../globals.php");
require_once("$srcdir/panel.inc");
require_once("$srcdir/options.inc.php");
//require_once("$srcdir/payment_jav.inc.php");

require_once("$srcdir/patient.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;



use OpenEMR\Common\Acl\AclMain;

$oemr_ui = new OemrUI($arrOeUiSettings);

if (isset($_GET['set_pid'])) {
	include_once("$srcdir/pid.inc");
	setpid($_GET['set_pid']);
}
////////////////////////////////////////////////////////////////
//post request section
//handle the post request for enroll or discharge a panel
$is_post_request = $_SERVER["REQUEST_METHOD"] == "POST";
$sql_date = (!empty($_POST['date'])) ? DateToYYYYMMDD($_POST['date']) : date('Y-01-01');

$_POST['form_details'] = true;


if($is_post_request){
	$request = $_POST['request'] ?? '';

	if($request == "enroll"){
		$panel['risk_stratification'] = $_POST['risk_stratification'] ?? '';
		$panel['panel_id'] = $_POST['sub_panels'];
		$panel['patient_id'] =  $pid ?? '';

		insertEnrolment($panel);
	} else if ($request == "discharge"){
		$enrollment_id = $_POST['enrollment_id'] ?? '';
		dischargePatient($enrollment_id);
	
	} else if ( $request == "delete"){
		$enrollment_id = $_POST['enrollment_id'] ?? '';
		deleteEnrollment($enrollment_id);
	
	
	
	} else if($request == "follow_up"){
		$followup['action_type'] = $_POST['action_type'];
		$followup['patient_id'] = $pid ?? '';
                $followup['panel_id'] = $_POST['panel_id'];
		$followup['date'] = $_POST['date'];

		if($followup['action_type'] == 'new'){
			insertFollowup($followup);	
		}
		if($followup['action_type'] == 'delete'){
                        deleteFollowup($followup);
                }
	}
}
//end of post request section
////////////////////////////////////////////////////////////////

$alertmsg = '';

?>
<html>
<head>

<style>
.highlight {
	color: green;
}
tr.selected {
	background-color: white;
}

#customers {
  	font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
  	border-collapse: collapse;
  	width: 100%;
}
#customers td, #customers th {
  	border: 1px #ddd;
  	padding: 8px;
}
#customers tr:nth-child(even){background-color: #f2f2f2;}
#customers tr:hover {background-color: #ddd;}
#customers tr:nth-child(even) { border-top: solid thin; }
#customers tr:nth-child(even) { border-bottom: solid thin; }
#customers th {
  	padding-top: 12px;
  	padding-bottom: 12px;
  	text-align: left;
  	background-color: white;
  	color: black;
}
input[type=submit] {
  	background-color: #006633;
  	border: none;
  	color: white;
  	text-decoration: none;
  	margin: 4px 2px;
  	cursor: pointer;
}
input[type=submit]:hover {
  	background-color: #006633;
}
/*for collaps used in the panels table */
.collapsible {
  	cursor: pointer;
  	padding: 18px;
  	width: 100%;
  	border: none;
  	text-align: left;
  	outline: none;
  	font-size: 15px;
}
/*
.active, .collapsible:hover {
  	background-color: #555;
}
*/
.content {
  	padding: 0 18px;
  	display: none;
  	overflow: hidden;
  	background-color: #f1f1f1;
}

.PanelHead{
  	background-color: #777;
  	color: white;
  	cursor: pointer;
}
/*
.active, .PanelHead:hover {
	background-color: #555;
}
*/

#form_background {
  	border-radius: 5px;
  	background-color: #f2f2f2;
  	padding: 20px;
}

#date_input {
	 width: 40%;
} 


input[type=text], select {
  	width: 100%;
  	padding: 12px 20px;
  	margin: 8px 0;
  	display: inline-block;
  	border: 1px solid #ccc;
  	border-radius: 4px;
  	box-sizing: border-box;
}
</style>

<title><?php echo xlt("Panels"); ?></title>
        <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

        <script language="javascript" type="text/javascript">

            $(function() {
                $('.datetimepicker').datetimepicker({
                    <?php $datetimepicker_timepicker = false; ?>
                    <?php $datetimepicker_showseconds = true; ?>
                    <?php $datetimepicker_formatInput = true; ?>
                    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
                });
            });

        </script>
<!--This scrept for discharge a pation from a panels
It is called in the table discharge a tage-->
<script>
function testFunction(panel) {
	if (confirm("Do you want to discharge from "+panel+"?")) {
		return true ;
	} else {
		return false ;
	}
}

function delete_panel(panel) {
        if (confirm("Do you want to delete "+panel+"?")) {
                return true ;
        } else {
                return false ;
		}
}
$( document ).ready(function() {
	//collapse and expand section
	$('.breakrow').click(function(){
      	$(this).nextUntil('tr.breakrow').slideToggle(200);
	});
});

function checkform() {
    	if(document.enrolment.panels.value == "select_panel") {
		alert("please select a panel");
        	return false;
	} else if(document.enrolment.sub_panels.value == "select_sub_panel") {
                alert("please select a sub panel");
                return false;

	} else {
		document.enrolment.submit();
	}
}

function addSubPanels(ids,titles) {
	var x = document.getElementById("panels").value;
	var i;
	var text="<select name='sub_panels' id='sub_panels'>";
	for (i = 0; i < ids.length; i++) {
		if (ids[i].substring(0, x.length) == x){

			text += "<option value='" + ids[i];
			text += "' id= ";
		       text += "'" +	ids[i];
			text += "' >";
		       text +=	titles[i];
 			text +=   "</option>";
	}	}
  	text  +="</select>";
  	document.getElementById("subPanelsDiv").innerHTML =  text;
}
</script>

</head>

<body class="body_top">
<div id="container_div" class="<?php echo $oemr_ui->oeContainer();?>">

<!-- Required for the popup date selectors -->
        <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<h2>Patient's Panels</h2>


<?php
////////////////////////////////////////////////////////////////

//display panels information
// check if the patien inrolled in any panels
if (isset($pid)) {
	$panels = getPanelsByPatient_id($pid,"all");
        if ($panels === -1 or sqlNumRows($panels)<1) {
		echo ("This patien is not inrolled in any panel</br></br>");
	}else {  //if the patient inrolled into a panel then print the table
		//print the table start
?>
<table id="customers">
<tr>
	<th>Case ID</th>
        <th>Panel</th>
        <th>Status</th>
        <th>Risk Stratification</th>
        <th>Enrollment Date</th>
        <th>Discharge Date</th>
        <th colspan="2">Next Follow Up Date</th>
</tr>

<?php
// print the panels info for the selected pation in a talbe format
while ($row = sqlFetchArray($panels)) {
	$SubPanels = getPatientSubPanelsInfo($pid,$row['id'],"all");
	echo $SupPanels;
?>

<tr class="breakrow">
<?php // Case ID is a unique number for active panels
//that is a combination of patient id and panel id ?>
<td colspan="1" class="PanelHead"><b><?php echo attr($pid), attr($row['id']); ?></b></td>
<td colspan="5" class="PanelHead"><b><?php echo attr($row['panel']); ?></b></td>
<td colspan="2" class="PanelHead"><b>
<?php  //change bellow  code  with  the last script  code ?>
<?php	$next_follow_up =sqlFetchArray(getFollowUpDate(attr($pid), attr($row['id'])));
			$follow_up_value = $next_follow_up['follow_up_date'];
	
			if (empty($follow_up_value)) {
				echo " ";
			} else {
			echo oeFormatShortDate(attr($follow_up_value)); } 
?></b></td>
		


</tr>
<?php while ($row = sqlFetchArray($SubPanels)) { ?>
	<tr class="datarow">
		<td><?php echo "" ?>
<form action="#" method="post">
        <input type="hidden" name="request" value="delete" />
        <input type="hidden" name="enrollment_id" value="<?php echo attr($row['id']); ?>" />

        <button type="submit"  style="background-color: Transparent;border: none;"
                onClick="return delete_panel('<?php echo attr($row['panel']) . ": " . attr($row['sub_panel']); ?>')" >

   <i class="fa fa-trash-o" style="font-size:24px;color:red"></i>
</button>
</form>
</td>
             	<td><?php echo attr($row['sub_panel']); ?></td>
             	<td><?php echo attr($row['status']); ?></td>
             	<td><?php echo attr($row['risk_stratification']); ?></td>
             	<td><?php echo oeFormatShortDate(attr($row['enrollment_date'])); ?></td>
		<td><?php 
		if (empty($row['discharge_date'])) {
                        echo " ";
                } else {
			echo oeFormatShortDate(attr($row['discharge_date']));
		}	
		?></td>

		<td>&nbsp;</td>
<td>
<?php //display the dischrged button only if the panel status is active
if($row['status'] == 'Active'){?>

<form action="#" method="post">
	<input type="hidden" name="request" value="discharge" />
        <input type="hidden" name="enrollment_id" value="<?php echo attr($row['id']); ?>" />
        <input type="submit" value="Discharge"
		onClick="return testFunction('<?php echo attr($row['panel']) . ": " . attr($row['sub_panel']); ?>')" />
</form>
<?php } else { echo "&nbsp;"; } ?>
</td></tr>
<?php } // end the while loop?>
<?php } // end the while loop?>
</table>
</br></br>
<?php } // end the if isset pid
  //End of display panels information
}//end of print the table
////////////////////////////////////////////////////////////////
?>

<?php //adding the patient into a new panels ?>
<table>
<tr>
    <th width="50%"> 
<div id="form_background">
<form action="#" method="post" name="enrolment"  onsubmit="return checkform()" >
<h3>Enroll to a panel</h3>
<?php

$panels = getAllPanels();

$subpanels = getAllSubPanels();

$ids = [];
$titles = [];
while($row = sqlFetchArray($subpanels))
{
	$titles[] = $row['title'];
	$ids []= $row['option_id'];
}
?>
<b><label for="panel">Select the panel:</label></b>
<select name="panels" id="panels" onchange='addSubPanels( <?php echo json_encode($ids); ?> ,  <?php echo json_encode($titles); ?>)'>
<option value= "select_panel" id="select_panel" selected disabled>Select Panel</option>
<?php
while ($row = sqlFetchArray($panels)) {
	echo "<option value=\"" . attr($row['option_id']) . "\"";
      	echo "id=\"" . attr($row['option_id']) . "\"";
      	echo ">";
      	echo attr($row['title']) . "</option>";
} ?>
</select>

<b><label for="sub_panels">Sub Panels</label></b>
<div id ="subPanelsDiv">
      <!-- cod from javacript will be past here -->
</div>
</br>
<b><label for="risk_stratification">Select the risk stratification:</label></b>
<select name="risk_stratification">
    <option value="High">High</option>
    <option value="Moderate" selected>Moderate</option>
    <option value="Low">Low</option>
</select>

<input type="hidden" name="request" value="enroll" />
<input type="submit" value="Enroll Patient"/>

</form>
</div> <!-- end of the form -->
</th> <th></th>
<th align="left" width="45%">
<!-- Start the Schedule appointment form -->
<div  id="form_background">

<form name='theform' id='theform' method='post' action='panels.php' onSubmit="return Form_Validate();">

<h3>Schedule follow up</h3>
<b><label for="action_type">Select Action:</label></b>
<select name="action_type" id="action_type" >
<option value="new"  >New Appointment</option>
<!-- <option value="edit" >Edit Appointment</option> -->
<option value="delete" >Cancel Appointment</option>
</select>

<?php $panels = getAllPanels(); ?>
<b><label for="panel">Select the panel:</label></b>
<select name="panel_id" id="panel_id" >
<option value= "select_panel" id="select_panel" selected disabled>Select Panel</option>
<?php
while ($row = sqlFetchArray($panels)) {
	echo "<option value=\"" . attr($row['option_id']) . "\"";
      	echo "id=\"" . attr($row['option_id']) . "\"";
      	echo ">";
      	echo attr($row['title']) . "</option>";
} ?>
</select>
<b><label for="date">Select date</label>
<input  type='text' class='datetimepicker form-control' name='date'  id="date"
	 value='<?php	$next_follow_up =sqlFetchArray(getFollowUpDate(attr($pid), attr($row['id'])));
			$follow_up_value = $next_follow_up['follow_up_date'];

			if (empty($follow_up_value)) {
				echo " ";
			} else {
			echo date("m-d-Y", strtotime(attr($follow_up_value))); }
		?>' />

<input type="hidden" name="request" value="follow_up" />
<input type="submit" value="Submit"/>
</form>
</div>
</th>
</tr>
</table>
<?php //end of the adding panels section ?>

</div>
</body>

</html>
