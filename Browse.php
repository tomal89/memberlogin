<?php
session_start();
echo "my name is khan ";
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
	require('Includes/I_FileInclude.php');           // file include function	
	require('Includes/I_NameCombine.php');           // function to combine member name into a single string in a nice way - used to combine FName and Suffix	
	require('Includes/I_Convert_DOB_to_Age.php');	 // Converts raw DOB (in the MySQL yyyy-mm-dd format) to age in years.
	require('Includes/I_ConvertDate_1.php');		 // function to convert date format from (yyyy mm dd) TO (dd mmm yyyy) input must be a date in yyyy-mm-dd format.
													 // Options: 1 = January, 2 = JAN
	require('Includes/I_Convert_Zip.php');			 // Converts Zip from raw format to 5+4 format.
	require('Includes/I_Common_HNav_Page.php');	 	 // function to set horizontal menu in base page.
	require('Includes/I_CountRecords.php');	 		 // Counts rows in a table.
	require('Includes/I_SortIndicator.php');	 	 // Evaluates where to put the indicator (an image) for which field the result set is sorted on.
	require('Includes/I_CalcVar_Z.php');	 		 // Caluculates a zero-based value for a var. (i.e. if X is 1, X_Z = 0).
	require('Includes/I_RecordNavBar.php');	 		 // Stuff for the RecordNavBar. Requires CalcVar_Z to work.
	require('Includes/I_CalcEndOfNextMonthUnix.php');// Stuff for the RecordNavBar. Requires CalcVar_Z to work.
	require('Includes/I_CalcWarningFlag.php');	 	 // Uses ExpDate to calc. a flag. Returns Red, Yellow or None. Requires CalcEndOfNextMonth to work.

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

	$Browse_BodyHead_Member = 'Templates/Browse/T_Browse_BodyHead_Member.php';
	$Browse_BodyRepeat_Member = 'Templates/Browse/T_Browse_BodyRepeat_Member.php';
	$Browse_BodyFoot_Member = 'Templates/Browse/T_Browse_BodyFoot_Member.php';

	$Browse_BodyHead_Group = 'Templates/Browse/T_Browse_BodyHead_Group.php';
	$Browse_BodyRepeat_Group = 'Templates/Browse/T_Browse_BodyRepeat_Group.php';
	$Browse_BodyFoot_Group = 'Templates/Browse/T_Browse_BodyFoot_Group.php';


	// Validations
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
	if ((($P_G == 1) || ($P_G == 2) || ($P_P == 1) || ($P_P == 2)) && (($P_G == $P_S) || ($P_P == $P_S)))
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
	$ButtonArray[1] = array( '1', 'Browse.php?P=1', 'Members');
	$ButtonArray[2] = array( '2', 'Browse.php?P=2', 'Groups');


//print("<br>Session:<pre>");
//print_r($_SESSION);
//print("</pre><br>");


	// Calculations
	//
	// records in the db are zero based so need to convert to that. (Z for Zero)
	$CurrentS_Z = CalcVar_Z($CurrentS);


// do the appropriate SQL query and replace the appropriate fields in the Body section
// $P 1 & 2 are "main" pages using the base HTML template
//
//

	// Set the base template
	$output = Common_HNav_Page($CurrentP, $ButtonArray);
	$output = str_replace( "['SectionTitleLeft']", 'Browse', $output);
	$output = str_replace( "['SectionTitleRight']", '', $output);
	$output = str_replace( "['SectionSubjectLeft']", '', $output);
	$output = str_replace( "['SectionSubjectRight']", '', $output);

	switch($CurrentP)
	{
		case 1 : // Browse by members

			// Set the Sort vars
			$SortArray[1] = 'Member.MemberID';
			$SortArray[2] = 'Member.LName';
			$SortArray[3] = 'Member.FName';
			$SortArray[4] = 'Member.DOB';
			$SortArray[5] = 'Member.Gender';
			$SortArray[6] = 'FamOrg.Name';
			$SortArray[7] = 'MemberType.MemberType';

			// Build Page
			
				//Build Body
				$Body = FileInclude($Browse_BodyHead_Member);

				// the query body
				$query = 	 "Member
							 LEFT JOIN FamOrg ON (Member.FamOrgID = FamOrg.FamOrgID)
							 LEFT JOIN MemberType ON (FamOrg.MemberTypeID = MemberType.MemberTypeID)
							 WHERE (FamOrg.FamOrgArchive = '0')";


				// Need to count the number of rows
				$TotalRecords = CountRecords($query);

				// Do query and add in query results
				$QueryHead = "SELECT Member.MemberID, Member.FamOrgID, Member.PName, Member.FName, Member.LName, Member.Suffix, Member.DOB, Member.Gender, FamOrg.Name, MemberType.MemberType
							 FROM ";
				$QueryFoot = " ORDER BY ".$SortArray[$CurrentSort]." LIMIT ".$CurrentS_Z.",".$CurrentL;

				$query = $QueryHead.$query.$QueryFoot;

				$result = mysql_query($query);
				// error check
				if(!$result)
				{echo '<br />error - could not complete process the query. My deepest apologies.<br />';}

				// Set the Body Repeat file
				$BodyRepeatSection = FileInclude($Browse_BodyRepeat_Member);

				while($row = mysql_fetch_array($result))
				{
					foreach($row as $key => $val)
					{$$key = stripslashes($val);} 

					// compute FNameSuffix
					$FNameSuffix = MemberName($PName, $FName, '', '', $Suffix);
				
					// compute Age
					$Age = Convert_DOB_to_Age($DOB);

					// Make new row
					$BodyRepeat = $BodyRepeatSection;

					// Replace placeholders in the new row
					$BodyRepeat = str_replace("['MemberID']", $MemberID, $BodyRepeat);
					$BodyRepeat = str_replace("['FamOrgID']", $FamOrgID, $BodyRepeat);
					$BodyRepeat = str_replace("['LName']", $LName, $BodyRepeat);
					$BodyRepeat = str_replace("['FNameSuffix']", $FNameSuffix, $BodyRepeat);
					$BodyRepeat = str_replace("['Age']", $Age, $BodyRepeat);
					$BodyRepeat = str_replace("['Gender']", $Gender, $BodyRepeat);
					$BodyRepeat = str_replace("['Name']", $Name, $BodyRepeat);
					$BodyRepeat = str_replace("['MemberType']", $MemberType, $BodyRepeat);
				
					// Add new row to body
					$Body .= $BodyRepeat;
				}
				
				// We've got the head and body. Now add footer to the Body section
				$Body .= fileInclude($Browse_BodyFoot_Member);
				
		break;

		case 2 : // Group
			// Build Page
			
			// Set the Sort vars
			$SortArray[1] = 'FamOrg.FamOrgID';
			$SortArray[2] = 'FamOrg.Name';
			$SortArray[3] = 'FamOrg.JoinDate';
			$SortArray[4] = 'FamOrg.ExpDate';
			$SortArray[5] = 'FamOrg.City';
			$SortArray[6] = 'FamOrg.StateTwoLetter';
			$SortArray[7] = 'FamOrg.Zip';
			$SortArray[8] = 'MemberType.MemberType';


				//Build Body
				$Body = FileInclude($Browse_BodyHead_Group);

				$query = 	 "FamOrg
							 LEFT JOIN MemberType ON (FamOrg.MemberTypeID = MemberType.MemberTypeID)
							 WHERE (FamOrg.FamOrgArchive = '0')";

				// Need to count the number of rows
				$TotalRecords = CountRecords($query);


				// Do query and add in query results
				$QueryHead = "SELECT FamOrg.FamOrgID, FamOrg.Name, FamOrg.JoinDate, FamOrg.ExpDate, FamOrg.City, FamOrg.StateTwoLetter, FamOrg.Zip, MemberType.MemberType FROM ";
				$QueryFoot = " ORDER BY ".$SortArray[$CurrentSort]." LIMIT ".$CurrentS_Z.",".$CurrentL;

				$query = $QueryHead.$query.$QueryFoot;

				$result = mysql_query($query);
				// error check
				if(!$result)
				{echo '<br />error - could not complete process the query. My deepest apologies.<br />';}

				// Set the Body Repeat file
				$BodyRepeatSection = FileInclude($Browse_BodyRepeat_Group);

				while($row = mysql_fetch_array($result))
				{
					foreach($row as $key => $val)
					{$$key = stripslashes($val);} 

					// CalcWarningFlag returns Red (Past), Yellow (Before end of next month) or None (Beyond end of next month) based on ExpDate. 
					$WarningFlag = CalcWarningFlag($ExpDate);

					//Convert Dates from MySQL format () to dd-mmm-yyyy format.
					$JoinDate = ConvertDateToDD_MMM_YYYY($JoinDate, 3);
					$ExpDate = ConvertDateToDD_MMM_YYYY($ExpDate, 3);

					// Convert Zip to 5+4
					$Zip = Convert_Zip($Zip);

					// Make new row
					$BodyRepeat = $BodyRepeatSection;

					// Replace placeholders in the new row
					$BodyRepeat = str_replace("['FamOrgID']", $FamOrgID, $BodyRepeat);
					$BodyRepeat = str_replace("['Name']", $Name, $BodyRepeat);
					$BodyRepeat = str_replace("['JoinDate']", $JoinDate, $BodyRepeat);
					$BodyRepeat = str_replace("['ExpDate']", $ExpDate, $BodyRepeat);
					$BodyRepeat = str_replace("['ExpDateColor']", $WarningFlag, $BodyRepeat);
					$BodyRepeat = str_replace("['City']", $City, $BodyRepeat);
					$BodyRepeat = str_replace("['StateTwoLetter']", $StateTwoLetter, $BodyRepeat);
					$BodyRepeat = str_replace("['Zip']", $Zip, $BodyRepeat);
					$BodyRepeat = str_replace("['MemberType']", $MemberType, $BodyRepeat);
				
					// Add new row to body
					$Body .= $BodyRepeat;
				}
				
				// We've got the head and body. Now add footer to the Body section
				$Body .= FileInclude($Browse_BodyFoot_Member);
		break;
	}


// RecordNavBar stuff - needs further encapsulation (maybe an IF statement and a flag?) but this is a start...
//

	// Bring in the NavBar
	if ($TotalRecords > 10)
	{
		// Do RecordNavBar calcs and such
		$NavBar = RecordNavBar($NavBar, $NavBar_LeftDiv, $NavBar_CenterDiv, $NavBar_RightDiv, $CurrentS, $CurrentL, $TotalRecords, "Browse.php");
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
