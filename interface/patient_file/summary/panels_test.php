<?php
/**
 * This report lists all the demographics allergies,problems
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Wejdan Bagais <w.bagais@gmail.com>
 * @copyright Copyright (c) 2021 Wejdan Bagais <w.bagais@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

'Y-m-d'
require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/panel.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\OeUI\OemrUI;

$oemr_ui = new OemrUI($arrOeUiSettings);

if (isset($_GET['set_pid'])) {
	include_once("$srcdir/pid.inc");
	setpid($_GET['set_pid']);
}

$sql_date = (!empty($_POST['date'])) ? DateToYYYYMMDD($_POST['date']) : date('Y-m-d');

////////////////////////////////////////////////////////////////
//post request section
//handle the post request for enroll or discharge a panel
$is_post_request = $_SERVER["REQUEST_METHOD"] == "POST";

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
	}
}
//end of post request section
////////////////////////////////////////////////////////////////

?>

<html>
     <head>
        <title>
            <?php echo xlt('Panels'); ?>
        </title>
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
  	background-color: #355615;
  	padding: 5px 10px;
  	border: none;
  	color: white;
  	text-decoration: none;
  	margin: 4px 2px;
  	cursor: pointer;
}
input[type=submit]:hover {
  	background-color: #409E2D;
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

.active, .PanelHead:hover {
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

#form_background {
  	border-radius: 5px;
  	background-color: #f2f2f2;
  	padding: 20px;
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

</head> 
<body class="body_top">
        <!-- Required for the popup date selectors -->
<form name='theform' id='theform' method='post' action='panels_report.php' onSubmit="return Form_Validate();">
	<table>	
       	 <tr>
            <td><input type='text' class='datetimepicker form-control' name='date' id="date" size='18' value='<?php echo attr(oeFormatDateTime($sql_date, 0, true)); ?>'></td>
         </tr>
         </table>
</form>

</body>

</html>
