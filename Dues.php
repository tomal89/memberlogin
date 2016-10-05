<?php
session_start();

//print("<table border='1'><tr><td>Session:<pre>");
//print_r($_SESSION);
//print("</pre></td>");

//print("<td>Get:<pre>");
//print_r($_GET);
//print("</pre></td>");

//print("<td>Post:<pre>");
//print_r($_POST);
//print("</pre></td></tr>");


	require('Includes/I_DBConnect.php');             // db connection stuff
	require('Includes/I_Common_HNav_Page.php');	 	 // function to set horizontal menu in base page.
	require('Includes/I_FileInclude.php');           // file include function	
	require('Includes/I_ConvertDate_1.php');		 // function to convert date format from (yyyy mm dd) TO (dd mmm yyyy) input must be a date in yyyy-mm-dd format.
													 // Options: 1 = January, 2 = JAN
	require('Includes/I_CountRecords.php');	 		 // Counts rows in a table.
	require('Includes/I_SortIndicator.php');	 	 // Evaluates where to put the indicator (an image) for which field the result set is sorted on.
	require('Includes/I_CalcVar_Z.php');	 		 // Caluculates a zero-based value for a var. (i.e. if X is 1, X_Z = 0).
	require('Includes/I_RecordNavBar.php');	 		 // Stuff for the RecordNavBar. Requires CalcVar_Z to work.
	require('Includes/I_CalcEndOfNextMonthUnix.php');// Stuff for the RecordNavBar. Requires CalcVar_Z to work.
	require('Includes/I_CalcWarningFlag.php');	 	 // Uses ExpDate to calc. a flag. Returns Red, Yellow or None. Requires CalcEndOfNextMonth to work.
	require('Includes/I_ArchiveActivateGroup.php');	 // Simple function to archive/unarchive a group.

	require('Includes/I_Validate_Vars.php');		 // Validates L (# of records/page). Local value of L is "$CurrentL"
													 // Validates S (Starting record). Local value of S is "$CurrentS". Default is 1
	require('Includes/I_Validations.php');			 // Mother of all validation includes. Functions for validating/cleaning ALL data.
	require('Includes/I_SetErrorArrayFlag.php');	 // Checks the ErrorArray. If there are error codes, returns 1. Else returns 0.
	require('Includes/I_ReplaceErrorCodes.php');	 // Replaces error codes in the output with the appropriate error messages.



	// Set the various templates here	
	$NavBar = 'Templates/Common/T_Common_NavBar.php';
	$NavBar_LeftDiv = 'Templates/Common/T_Common_NavBar_LeftDiv.php';
	$NavBar_CenterDiv = '';
	$NavBar_RightDiv = 'Templates/Common/T_Common_NavBar_RightDiv.php';

	$Dues_BodyHead = 'Templates/Dues/T_Dues_BodyHead.php';
	$Dues_BodyRepeat = 'Templates/Dues/T_Dues_BodyRepeat.php';
	$Dues_BodyFoot = 'Templates/Dues/T_Dues_BodyFoot.php';

	$ArchiveButton = 'Templates/Common/T_Common_ArchiveButton.php';


	// Validations
	// Validate the Current A (Archive Flag)
	// $A comes from URL, $A_D = default value (0)
	$A_D = 0;
	$A_G = $_GET['A'];
	$A_S = '';
	$A_P = $_POST['S'];
	$CurrentA = Validate_Vars($A_G, '', $A_P, $A_D);
	list($CurrentA, $ErrorArray) = CleanNumber($CurrentA, 'CurrentA', 'Current Archive Flag', 1, 1, $ErrorArray);

	// CurrentFamOrgID (F)
	$F_D = ''; // Need some sort of error processor for if this happens.
	$F_S = '';
	$F_P = $_POST['F'];
	$F_G = $_GET['F'];
	$CurrentFamOrgID = Validate_Vars($F_G, $F_S, $F_P, $F_D);
	if($CurrentFamOrgID) {list($CurrentFamOrgID, $ErrorArray) = CleanNumber($CurrentFamOrgID, 'CurrentFamOrgID', '', 4, 0, $ErrorArray);}

	// Validate the Current S (Starting record to show)
	// $S comes from URL, $S_D = default value (1)
	$S_D = 1;
	$S_G = $_GET['S'];
	$S_S = $_SESSION['S'];
	$S_P = $_POST['S'];
	$CurrentS = Validate_Vars($S_G, $S_S, $S_P, $S_D);
	list($CurrentS, $ErrorArray) = CleanNumber($CurrentS, 'CurrentS', '', 4, 0, $ErrorArray);
	// Set current session value for S to the current S.
	$_SESSION['S'] = '';

	// Validate the Current P (Page)
	// Default for P is 1 which is the Member. Might want to change in future?
	$P_D = 1;
	$P_G = $_GET['P'];
	$P_S = $_SESSION['SessionP'];
	$P_P = $_POST['P'];
	$CurrentP = Validate_Vars($P_G, '', $P_P, $P_D);
	list($CurrentP, $ErrorArray) = CleanNumber($CurrentP, 'CurrentP', '', 2, 0, $ErrorArray);
	$_SESSION['SessionP'] = $CurrentP;

	// Validate the Current L (# of records to show)
	// $L comes from URL, $L_D = default value (20)
	$L_D = 10;
	$L_G = $_GET['L'];
	$L_S = $_SESSION['SessionL'];
	$L_P = $_POST['L'];
	if (((($P_G >= 1) && ($P_G <= 4)) || (($P_P >= 1) && ($P_P <= 4))) && (($P_G == $P_S) || ($P_P == $P_S)))
	{$CurrentL = Validate_Vars($L_G, $L_S, $L_P, $L_D);}
	else
	{$CurrentL = $L_D;}
	list($CurrentL, $ErrorArray) = CleanNumber($CurrentL, 'CurrentL', '', 4, 0, $ErrorArray);
	$_SESSION['SessionL'] = $CurrentL;


	// Validate the Current Sort
	// $Sort comes from URL, $Sort_D = default value (2)
	$Sort_D = 2;
	$Sort_G = $_GET['Sort'];
	$Sort_S = $_SESSION['Sort'];
	$Sort_P = $_POST['Sort'];
	if ($P_G == '' || $P_G != $P_S) // basically, if we're coming from somewhere else, set Sort to default.
	{$CurrentSort = $Sort_D;}
	else {$CurrentSort = Validate_Vars($Sort_G, $Sort_S, $Sort_P, $Sort_D);}
	list($CurrentSort, $ErrorArray) = CleanNumber($CurrentSort, 'CurrentSort', '', 1, 1, $ErrorArray);
	$_SESSION['Sort'] = $CurrentSort;

	//Now check the ErrorArray and if there are errors, do an error routine
	$ErrorFlag = SetErrorArrayFlag($ErrorArray);
	if ($ErrorFlag == '') {$ErrorArray = '';}
	else
	{exit("<b>There was an error. Try again later. Sorry.</b>");}


	// Set ButtonArrays
	$ButtonArray[1] = array( '1', 'Dues.php?P=1', 'All');
	$ButtonArray[2] = array( '2', 'Dues.php?P=2', 'Current');
	$ButtonArray[3] = array( '3', 'Dues.php?P=3', 'Due Soon');
	$ButtonArray[4] = array( '4', 'Dues.php?P=4', 'Expired');


//print("<br>Session:<pre>");
//print_r($_SESSION);
//print("</pre><br>");


	// Calculations
	//
	// records in the db are zero based so need to convert to that. (Z for Zero)
	$CurrentS_Z = CalcVar_Z($CurrentS);

	// Need to calculate dates
	$EndOfNextMonthDate = date('Y-m-d', CalcEndOfNextMonthUnix());
	$TodayDate = date('Y-m-d');


// If Archive Flag is present (A=2), archive the current FamOrgID and then process the page (P)
if ($CurrentA == 1)
	{ ArchiveActivateGroup(1, $CurrentFamOrgID); }


// do the appropriate SQL query and replace the appropriate fields in the Body section
// $P 1 to 4 are "main" pages using the base HTML template
//
//
	// Set the base template
	$output = Common_HNav_Page($CurrentP, $ButtonArray);
	$output = str_replace( "['SectionTitleLeft']", 'Dues', $output);
	$output = str_replace( "['SectionTitleRight']", '', $output);
	$output = str_replace( "['SectionSubjectLeft']", '', $output);
	$output = str_replace( "['SectionSubjectRight']", '', $output);

	// Set the Sort vars
	$SortArray[1] = 'FamOrg.FamOrgID';
	$SortArray[2] = 'FamOrg.Name';
	$SortArray[3] = 'MemberType.MemberType';
	$SortArray[4] = 'FamOrg.JoinDate';
	$SortArray[5] = 'FamOrg.ExpDate';

	switch($CurrentP)
	{
		case 1 : // Show All members regardless of dues due status
				$query =	"FamOrg
							 LEFT JOIN MemberType ON (FamOrg.MemberTypeID = MemberType.MemberTypeID)
							 WHERE (FamOrg.FamOrgArchive = '0')";
		break;
		case 2 : // Show Current members only
				$query =	"FamOrg
							 LEFT JOIN MemberType ON (FamOrg.MemberTypeID = MemberType.MemberTypeID)
							 WHERE (FamOrg.FamOrgArchive = '0') AND
							 (FamOrg.ExpDate >= '$EndOfNextMonthDate')";
		break;
		case 3 : // Show Due Soon members only
				$query =	"FamOrg
							 LEFT JOIN MemberType ON (FamOrg.MemberTypeID = MemberType.MemberTypeID)
							 WHERE (FamOrg.FamOrgArchive = '0') AND
							 (FamOrg.ExpDate < '$EndOfNextMonthDate') AND
							 (FamOrg.ExpDate > '$TodayDate')";
		break;
		case 4 : // Show Expired members only
				$query =	"FamOrg
							 LEFT JOIN MemberType ON (FamOrg.MemberTypeID = MemberType.MemberTypeID)
							 WHERE (FamOrg.FamOrgArchive = '0') AND
							 (FamOrg.ExpDate NOT LIKE '0000-%') AND
							 (FamOrg.ExpDate < '$TodayDate')";
		break;
	}


			// Build Page
			
				//Build Body
				$Body = FileInclude($Dues_BodyHead);

				// Need to count the number of rows
				$TotalRecords = CountRecords($query);

				// need to do some string manipulation for the query & total record query
				$QueryHead = "SELECT FamOrg.FamOrgID, FamOrg.MemberTypeID, FamOrg.Name, FamOrg.JoinDate, FamOrg.ExpDate, MemberType.MemberType
							 FROM ";
				$QueryFoot = " ORDER BY ".$SortArray[$CurrentSort]." LIMIT ".$CurrentS_Z.",".$CurrentL;

				$query = $QueryHead.$query.$QueryFoot;

				$result = mysql_query($query);
				// error check
				if(!$result)
				{echo '<br />error - could not process the query. My deepest apologies.<br />';}

				// Set the Body Repeat files
				$BodyRepeatSection = FileInclude($Dues_BodyRepeat);
				$ArchiveButton = FileInclude($ArchiveButton);

				while($row = mysql_fetch_array($result))
				{
					foreach($row as $key => $val)
					{$$key = stripslashes($val);} 

					// CalcWarningFlag returns Red (Past), Yellow (Before end of next month) or None (Beyond end of next month) based on ExpDate. 
					$WarningFlag = CalcWarningFlag($ExpDate);
					
					// Determine if the Archive button should be present or not (only present for red)
					if ($WarningFlag == 'Red') {$ArchiveButtonRepeat = $ArchiveButton;}
					else {$ArchiveButtonRepeat = "&nbsp;";}

					//Convert Dates
					$JoinDate = ConvertDateToDD_MMM_YYYY($JoinDate, 3);
					$ExpDate = ConvertDateToDD_MMM_YYYY($ExpDate, 3);

					// Make new row
					$BodyRepeat = $BodyRepeatSection;

					// Replace placeholders in the new row
					$BodyRepeat = str_replace("['ArchiveButton']", $ArchiveButtonRepeat, $BodyRepeat);
					$BodyRepeat = str_replace("['FormAction']", 'Dues.php', $BodyRepeat);
					$BodyRepeat = str_replace("['FamOrgID']", $FamOrgID, $BodyRepeat);
					$BodyRepeat = str_replace("['MemberID']", $MemberID, $BodyRepeat);
					$BodyRepeat = str_replace("['Name']", $Name, $BodyRepeat);
					$BodyRepeat = str_replace("['JoinDate']", $JoinDate, $BodyRepeat);
					$BodyRepeat = str_replace("['ExpDate']", $ExpDate, $BodyRepeat);
					$BodyRepeat = str_replace("['ExpDateColor']", $WarningFlag, $BodyRepeat);
					$BodyRepeat = str_replace("['MemberType']", $MemberType, $BodyRepeat);
				
					// Add new row to body
					$Body .= $BodyRepeat;
				}

				// We've got the head and body. Now add footer to the Body section
				$Body .= FileInclude($Dues_BodyFoot);



// RecordNavBar stuff - needs further encapsulation (maybe an IF statement and a flag?) but this is a start...
//

	// Bring in the NavBar
	if ($TotalRecords > 10)
	{
		// Do RecordNavBar calcs and such
		$NavBar = RecordNavBar($NavBar, $NavBar_LeftDiv, $NavBar_CenterDiv, $NavBar_RightDiv, $CurrentS, $CurrentL, $TotalRecords, "Dues.php");
	}
	else
	{
		$NavBar = '';
	}

	// Now do the replacements to the Browse page
	$output = str_replace("['Body']", $Body, $output);
	$output = str_replace("['NavBar']", $NavBar, $output);
	$output = str_replace("['P']", $CurrentP, $output);
	$output = str_replace("['L']", $CurrentL, $output);
	$output = str_replace("['S']", $CurrentS, $output);


	// Sort
	//
	$SortCode = "<img src=\"Images/SortArrow.gif\" width=\"10\" height=\"10\" border=\"0\">";
	$output = SortIndicator($CurrentSort, $SortCode, $SortArray, $output);


echo $output;
?>