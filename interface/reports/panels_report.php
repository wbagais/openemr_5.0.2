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


require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/payment_jav.inc.php");
require_once("$srcdir/panel.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$_POST['form_details'] = true;

$sql_date_from = (!empty($_POST['date_from'])) ? DateToYYYYMMDD($_POST['date_from']) : date('Y-01-01');
$sql_date_to = (!empty($_POST['date_to'])) ? DateToYYYYMMDD($_POST['date_to']) : date('Y-m-d');

$patient_id = trim($_POST["patient_id"]);
$status = trim($_POST["status"]) ?? 'Active';
$follow_up_in = trim($_POST["follow_up_in"]) ?? 'All';
?>
<html>
    <head>

        <title>
            <?php echo xlt('Panels Report'); ?>
        </title>

        <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

        <script language="JavaScript">
            function Form_Validate() {
                var d = document.forms[0];
                FromDate = d.date_from.value;
                ToDate = d.date_to.value;
                if ( (FromDate.length > 0) && (ToDate.length > 0) ) {
                    if ( FromDate > ToDate ){
                        alert(<?php echo xlj('To date must be later than From date!'); ?>);
                        return false;
                    }
                }
                $("#processing").show();
                return true;
            }
        </script>

        <style type="text/css">
            /* specifically include & exclude from printing */
            @media print {
                #report_parameters {
                    visibility: hidden;
                    display: none;
                }
                #report_parameters_daterange {
                    visibility: visible;
                    display: inline;
                }
                #report_results table {
                    margin-top: 0px;
                }
                #report_image {
                    visibility: hidden;
                    display: none;
                }
            }

            /* specifically exclude some from the screen */
            @media screen {
                #report_parameters_daterange {
                    visibility: hidden;
                    display: none;
                }
	    }
#td:visited {
  color: blue;
  background-color: transparent;
  text-decoration: none;
}

#td:hover {
  color: red;
  background-color: transparent;
  text-decoration: underline;
}
        </style>
        <script language="javascript" type="text/javascript">
            function submitForm() {
                var d_from = new String($('#date_from').val());
                var d_to = new String($('#date_to').val());

                var d_from_arr = d_from.split('-');
		var d_to_arr = d_to.split('-');

                var dt_from = new Date(d_from_arr[0], d_from_arr[1], d_from_arr[2]);
		var dt_to = new Date(d_to_arr[0], d_to_arr[1], d_to_arr[2]);


                //var mili_from = dt_from.getTime();
                //var mili_to = dt_to.getTime();
                //var diff = mili_to - mili_from;
		$('#date_error').css("display", "none");

                //if(diff < 0) //negative
                //{
                 //   $('#date_error').css("display", "inline");
                //}
                //else
                //{
                    $("#form_refresh").attr("value","true");
                    top.restoreSession();
                    $("#theform").submit();
                //}
            }

            //sorting changes
            function sortingCols(sort_by,sort_order)
            {
                $("#sortby").val(sort_by);
                $("#sortorder").val(sort_order);
                $("#form_refresh").attr("value","true");
                $("#theform").submit();
            }

            $(function() {
                $(".numeric_only").keydown(function(event) {
                    //alert(event.keyCode);
                    // Allow only backspace and delete
                    if ( event.keyCode == 46 || event.keyCode == 8 ) {
                        // let it happen, don't do anything
                    }
                    else {
                        if(!((event.keyCode >= 96 && event.keyCode <= 105) || (event.keyCode >= 48 && event.keyCode <= 57)))
                        {
                            event.preventDefault();
                        }
                    }
                });

                $('.datetimepicker').datetimepicker({
                    <?php $datetimepicker_timepicker = false; ?>
                    <?php $datetimepicker_showseconds = true; ?>
                    <?php $datetimepicker_formatInput = true; ?>
                    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
                });
            });

            function printForm(){
                 var win = top.printLogPrint ? top : opener.top;
                 win.printLogPrint(window);
            }
        </script>
    </head>

    <body class="body_top">
        <!-- Required for the popup date selectors -->
        <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
        <span class='title'>
        <?php echo xlt('Report - Patients List Panels');?>
        </span>
        <!-- Search can be done using range  filters.
        Search options include  procedure, prescription, medical history.
        -->

        <div id="report_parameters_daterange">
            <p>
            <?php echo "<span style='margin-left:5px;'><b>".xlt('Date Range').":</b>&nbsp;".text(oeFormatDateTime($sql_date_from, "global", true)) .
              " &nbsp; " . xlt('to') . " &nbsp; ". text(oeFormatDateTime($sql_date_to, "global", true))."</span>"; ?>
	    <span style="margin-left:5px; " ><b><?php echo xlt('Option'); ?>:</b>&nbsp;<?php echo text($_POST['srch_option']); ?></span>   
	 </p>
        </div>
        <form name='theform' id='theform' method='post' action='panels_report.php' onSubmit="return Form_Validate();">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <div id="report_parameters">
                <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
                <table>
                    <tr>
                    <td width='640px'>
                        <div class="cancel-float" style='float:left'>
                        <table class='text'>
			    <tr>
				<td class='control-label' ><b>Enrollment Date  </b><?php echo xlt('From'); ?>: </td>
                                <td><input type='text' class='datetimepicker form-control' name='date_from' id="date_from" size='18' value='<?php echo attr(oeFormatDateTime($sql_date_from, 0, true)); ?>'>
                                </td>
                                <td class='control-label'><?php echo xlt('To{{range}}'); ?>: </td>
                                <td><input type='text' class='datetimepicker form-control' name='date_to' id="date_to" size='18' value='<?php echo attr(oeFormatDateTime($sql_date_to, 0, true)); ?>'>
                                </td>
                                <td class='control-label'><?php echo xlt('Panels'); ?>: </td>
                                <td class='control-label'>
                                    <select class="form-control" name="srch_option" id="srch_option">

					<option> <?php echo xlt('All'); ?></option> 
					<?php
	   				 $panels = getAllPanels();
	    				while ($row = sqlFetchArray($panels)) { ?>
						<option  <?php echo ($_POST['srch_option'] == $row['option_id']) ? 'selected' : ''; ?> 
							value="<?php echo attr($row['option_id']); ?>" id=" <?php echo attr($row['option_id']) ; ?>">
						<?php echo attr($row['title']); ?> </option>
					<?php }  ?>
				    </select>		
                                    <?php ?>
                                </td>

                            </tr>
                            <tr>
                                <td class='control-label'><?php echo xlt('Patient ID'); ?>:</td>
                                <td><input name='patient_id' class="numeric_only form-control" type='text' id="patient_id" title='<?php echo xla('Optional numeric patient ID'); ?>' value='<?php echo attr($patient_id); ?>' size='10' maxlength='20' /></td>


			    	<td class='control-label'><?php echo xlt('Status'); ?>: </td>
				<td class='control-label'>
				<select class="form-control" name="status" id="status">
				<option  <?php echo ($_POST['status'] == 'All') ? 'selected' : ''; ?> value='All'  id = 'All'> <?php echo xlt('All'); ?></option>
				 <option  <?php echo (($_POST['status'] == 'Active') or $_POST['status'] == '') ? 'selected' : ''; ?> value='Active'  id = 'Active'> <?php echo xlt('Active'); ?></option>
				 <option  <?php echo ($_POST['status'] == 'Discharged') ? 'selected' : ''; ?> value='Discharged'  id = 'Discharged'> <?php echo xlt('Discharged'); ?></option>
			
</td>
				<td class='control-label'><?php echo xlt('Follow Up date'); ?>: </td>
				<td class='control-label'>
				<select class="form-control" name="follow_up_in" id="follow_up_in">
				<option  <?php echo (($_POST['follow_up_in'] == 'All') or $_POST['follow_up_in'] == '') ? 'selected' : ''; ?> value='All'  id = 'All'> <?php echo xlt('All'); ?></option>
				<option  <?php echo ($_POST['follow_up_in'] == 'Next Week') ? 'selected' : ''; ?> value='Next Week'  id = 'Next Week'> <?php echo xlt('Next Week'); ?></option>
				<option  <?php echo ($_POST['follow_up_in'] == 'Next Month') ? 'selected' : ''; ?> value='Next Month'  id = 'Next Month'> <?php echo xlt('Next Month'); ?></option>
				<option  <?php echo ($_POST['follow_up_in'] == 'No schedule') ? 'selected' : ''; ?> value='No schedule'  id = 'No schedule'> <?php echo xlt('No schedule'); ?></option>
</td>			</tr>	

                        </table>

                        </div></td>
                        <td height="100%" valign='middle' width="175"><table style='border-left:1px solid; width:100%; height:100%'>
                            <tr>
                                <td>
                                    <div class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href='#' class='btn btn-default btn-save' onclick='submitForm();'>
                                                <?php echo xlt('Submit'); ?>
                                            </a>
                                            <?php if (isset($_POST['form_refresh'])) {?>
                                                <a href='#' class='btn btn-default btn-print' onclick="printForm()">
                                                    <?php echo xlt('Print'); ?>
                                                </a>
                                            <?php }?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div id='processing' style='display:none;' ><img src='../pic/ajax-loader.gif'/></div>
                                </td>

                            </tr>
                        </table></td>
                    </tr>
                </table>
            </div>
        <!-- end of parameters -->
        <?php

        // SQL scripts for the various searches
        if ($_POST['form_refresh']) {
            //$sqlstmt = "";
		$sqlstmt = "SELECT e.* , l.title as sub_panel FROM panel_enrollment  as e ";
		$sqlstmt .= " JOIN list_options AS l ON e.panel_id = l.option_id  ";
		$sqlstmt .= " LEFT JOIN panel_follow_up as f ON SUBSTRING_INDEX(option_id, '_', 1)  = f.panel_id AND  e.patient_id = f.patient_id ";


            $srch_option = $_POST['srch_option'];
	    $sort = array("patient_id","panel_id","risk_stratification", "status", "enrollment_date", "discharge_date");
                    if ($sortby == "") {
                        $sortby = $sort[1];
                    }
            //from

            //WHERE Conditions started
	    $whr_stmt="WHERE list_id = \"Panel_Type\" ";
	    if($srch_option != 'All'){
		    $whr_stmt=$whr_stmt ." AND e.panel_id LIKE \"".  $srch_option . "%\"";
	    }

	    if (strlen($patient_id) != 0) {
                $whr_stmt = $whr_stmt."   and patient_id = \"" . $patient_id . "\"";
	    }


	   if (strlen($sql_date_from) != 0) {
                $whr_stmt = $whr_stmt."   and  enrollment_date >= \"" . $sql_date_from . "\"";
	   }


	     if (strlen($sql_date_to) != 0) {
                $whr_stmt = $whr_stmt."   and  enrollment_date <= \"" . $sql_date_to . "\"";
            }
		
	    
	    switch($status){
	    	case '':
	    	case 'Active':
		    $whr_stmt = $whr_stmt."   and status = \"Active\" ";
		    break;
	    	case 'Discharged':
		    $whr_stmt = $whr_stmt."   and status = \"Discharged\" ";
		    break;
	    	case 'All':
		    break;
	    }

	    //$follow_up_in = 'NNext/Last Follow Up Date	ext Month';
	    switch($follow_up_in){
	    case '':
		    break;
	    case 'Next Week':
		    $whr_stmt = $whr_stmt."   and f.follow_up_date >=  DATE(NOW()) AND f.follow_up_date <= DATE(NOW() + INTERVAL 7 DAY) ";
	    	    break;
	    
	    case 'No schedule':
                    $whr_stmt = $whr_stmt."   and (f.follow_up_date <=  DATE(NOW()) OR f.follow_up_date is NULL) ";
		    break;

	   case 'Next Month':
                    $whr_stmt = $whr_stmt."   and f.follow_up_date >=  DATE(NOW()) AND f.follow_up_date <= DATE(NOW() + INTERVAL 30 DAY)  ";
                    break;
	    }

            //Sorting By filter fields
            $sortby = $_POST['sortby'];
            $sortorder = $_POST['sortorder'];
	    $srch_option = "";
             // This is for sorting the records.

            if ($sortby == "") {
                $sortby = $sort[0];
            }

            if ($sortorder == "") {
                $sortorder = "asc";
            }

 	      for ($i = 0; $i < count($sort); $i++) {
                $sortlink[$i] = "<a href=\"#\" onclick=\"sortingCols(" . attr_js($sort[$i]) . ",'asc');\" ><img src='" .  $GLOBALS['images_static_relative'] . "/sortdown.gif' border=0 alt=\"".xla('Sort Up')."\"></a>";
	      }

            for ($i = 0; $i < count($sort); $i++) {
                if ($sortby == $sort[$i]) {
                    switch ($sortorder) {
                        case "asc":
                            $sortlink[$i] = "<a href=\"#\" onclick=\"sortingCols(" . attr_js($sortby) . ",'desc');\" ><img src='" .  $GLOBALS['images_static_relative'] . "/sortup.gif' border=0 alt=\"".xla('Sort Up')."\"></a>";
                            break;
                        case "desc":
                            $sortlink[$i] = "<a href=\"#\" onclick=\"sortingCols('" . attr_js($sortby) . "','asc');\" onclick=\"top.restoreSession()\"><img src='" . $GLOBALS['images_static_relative'] . "/sortdown.gif' border=0 alt=\"".xla('Sort Down')."\"></a>";
                            break;
                    } break;
                }
            }


            if (!empty($_POST['sortby']) && !empty($_POST['sortorder'])) {
                    $odrstmt = "ORDER BY ".escape_identifier($_POST['sortby'], $sort, true)." ".escape_sort_order($_POST['sortorder']);
                }

            //echo $sqlstmt."<hr>";
	    $sqlstmt=$sqlstmt." ".$whr_stmt." ".$odrstmt;
	    //echo $sqlstmt;
	    $result = sqlStatement($sqlstmt);
            //print_r($result);
            $row_id = 1.1;//given to each row to identify and toggle
            $img_id = 1.2;
            $k=1.3;

            if (sqlNumRows($result) > 0) {
                $patArr = array();

                $patDataArr = array();
                //$smoke_codes_arr = getSmokeCodes();
                while ($row = sqlFetchArray($result)) {
                        $patArr[] = $row['patient_id'];
                        $patInfoArr = array();
                        $patInfoArr['patient_id'] = $row['patient_id'];
			$patInfoArr['enrollment_date'] = $row['enrollment_date'];
			$patInfoArr['status'] = $row['status'];
			$patInfoArr['discharge_date'] = $row['discharge_date'];
			$patInfoArr['panel_id'] = $row['panel_id'];
			$patInfoArr['sub_panel'] = $row['sub_panel'];
			$patInfoArr['risk_stratification'] = $row['risk_stratification'];
			//Diagnosis Check
			$patFinalDataArr[] = $patInfoArr;
                }
                ?>

                <br>

                <input type="hidden" name="sortby" id="sortby" value="<?php echo attr($sortby); ?>" />
                <input type="hidden" name="sortorder" id="sortorder" value="<?php echo attr($sortorder); ?>" />
                <div id = "report_results">
                    <table>
                        <tr>
                            <td class="text"><strong><?php echo xlt('Total Number of Patients')?>:</strong>&nbsp;<span id="total_patients"><?php echo text(count(array_unique($patArr))); ?></span></td>
                        </tr>
                    </table>

                    <table width=90% align="center" cellpadding="5" cellspacing="0" style="font-family:tahoma;color:black;" border="0">

                    <?php
                    if (True) { ?>
                        <tr style="font-size:15px;">
			    <td width="7%"><b><?php echo xlt('Patient ID'); ?></b><?php echo $sortlink[0]; ?></td>
			    <td width="12%"><b><?php echo xlt('Patient\'s Name'); ?></b><?php echo $sortlink[1]; ?></td>
			    <td width="12%"><b><?php echo xlt('Patient\'s Date of Birth'); ?></b><?php echo $sortlink[2]; ?></td>
			    <td width="10%"><b><?php echo xlt('Panel ID'); ?></b><?php echo $sortlink[3]; ?></td>
			    <td colspan=2  width="10%"><b><?php echo xlt('Sub Panel'); ?></b><?php echo $sortlink[4]; ?></td>
			    <td width="10%"><b><?php echo xlt('risk_stratification'); ?></b><?php echo $sortlink[5]; ?></td>
		    	    <td width="13%"><b>Next/Last Follow Up Date</b></td>
			    <td width="6%"><b><?php echo xlt('Status'); ?></b><?php echo $sortlink[6]; ?></td>
                            <td width="10%"><b><?php echo xlt('Enrolment Date');?></b><?php echo $sortlink[7]; ?></td>
                            <td width="10%"><b><?php echo xlt('Discharge Date'); ?></b><?php echo $sortlink[8]; ?></td>
                        </tr>
                        <?php foreach ($patFinalDataArr as $patKey => $patDetailVal) { ?>
                                <tr bgcolor = "#CCCCCC" style="font-size:15px;">
				    <td id=td  onclick="location.href =' <?php echo '../patient_file/summary/demographics.php?set_pid=' . $patDetailVal['patient_id'] ; ?>'">
					<?php echo text($patDetailVal['patient_id']); ?></td>
				    <td > <?php $prow = getPatientData($patDetailVal['patient_id'], "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD"); 
						echo $prow['fname'] . " " . $prow['lname']; ?></td>
					<td><?php 
						 if (empty($prow['DOB_YMD'])) {
                                                echo " ";
                                        } else {							
						echo oeFormatShortDate(attr($prow['DOB_YMD']));}?> </td> 
					<?php $sqlpanl = "SELECT title FROM list_options WHERE list_id =  'Panel_Type' AND option_id = \"" . explode("_",  text($patDetailVal['panel_id']))[0] . "\"";
					$panel_title = sqlFetchArray(sqlStatement($sqlpanl));
					echo "<td>" .  $panel_title['title'] . "</td>";					
						?>
				    <td colspan=2 ><?php echo text($patDetailVal['sub_panel']); ?></td>
				    <td><?php echo text($patDetailVal['risk_stratification']); ?></td>
				    <td> <?php $next_follow_up =sqlFetchArray(getFollowUpDate(attr($patDetailVal['patient_id']), attr(explode("_",$patDetailVal['panel_id'])[0]), $last = True));
					$follow_up_value = $next_follow_up['follow_up_date'];
					if (empty($follow_up_value)) {
                                                echo " ";
                                        } else {
					echo oeFormatShortDate(attr($follow_up_value));}
				     ?>
				     <td ><?php echo text($patDetailVal['status']); ?></td>
				     <td ><?php
					if (empty($patDetailVal['enrollment_date'])) {
                                		echo " ";
                        		} else {
						echo oeFormatShortDate(attr($patDetailVal['enrollment_date']));} ?></td>
                                     <td ><?php if (empty($patDetailVal['discharge_date'])) {
                                                echo " ";
                                        } else {echo oeFormatShortDate(attr($patDetailVal['discharge_date']));} ?></td>
                                </tr>
                        <?php	} 
                    }  ?>

                    </table>
                     <!-- Main table ends -->
                <?php
            } else {//End if $result?>
                    <table>
                        <tr>
                            <td class="text">&nbsp;&nbsp;<?php echo xlt('No records found.')?></td>
                        </tr>
                    </table>
                <?php
            }
            ?>
                </div>

            <?php
        } else {//End if form_refresh
            ?><div class='text'> <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?> </div><?php
        }
        ?>
        </form>

    </body>
</html>

