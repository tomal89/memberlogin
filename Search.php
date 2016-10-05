<?php
	session_start();

//print("<Table width='75%'><tr><td width='25%'>Session:<pre>");
//print_r($_SESSION);
//print("</pre></td>");

//print("<td width='25%'>GET:<pre>");
//print_r($_GET);
//print("</pre></td>");

//print("<td width='25%'>POST:<pre>");
//print_r($_POST);
//print("</pre></td></tr></table>");



	require('Includes/I_DBConnect.php');             // db connection stuff
	require('Includes/I_CleanData.php');             // Making data safe for the db	
	require('Includes/I_FileInclude.php');           // file include function	
	require('Includes/I_NameCombine.php');           // function to combines member name into a single string in a nice way - used when P=2	
	require('Includes/I_ConvertDate_1.php');		 // function to convert date format from (yyyy mm dd) TO (dd mmm yyyy) input must be a date in yyyy-mm-dd format.
													 // Options: 1 = January, 2 = JAN
	require('Includes/I_Convert_Zip.php');			 // Converts Zip from raw format to 5+4 format.
	require('Includes/I_Convert_DOB_to_Age.php');	 // Converts raw DOB (in the MySQL yyyy-mm-dd format) to age in years.
	require('Includes/I_LookUp.php');				 // function to find one field value from a table.
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
//	$Search_NavBar_Simple = 'Templates/Search/T_Search_NavBar_Simple.php';
//	$Search_NavBar_Advanced = 'Templates/Search/T_Search_NavBar_Advanced.php';

	$NavBar = 'Templates/Common/T_Common_NavBar.php';
	$NavBar_LeftDiv = 'Templates/Common/T_Common_NavBar_LeftDiv.php';
	$NavBar_CenterDiv = 'Templates/Search/T_Search_NavBar_Simple.php';
	$NavBar_RightDiv = 'Templates/Common/T_Common_NavBar_RightDiv.php';


//	$Search_NavBar_Simple2 = 'Templates/Search/T_Search_NavBar_Simple2.php'; // for when results <11
	
	$Search_BodyDefault_Simple = 'Templates/Search/T_Search_BodyDefault_Simple.php';
	$Search_BodyDefault_Advanced = 'Templates/Search/T_Search_BodyDefault_Advanced.php';
	
	$Search_BodyHead_Simple_Member = 'Templates/Search/T_Search_BodyHead_Member.php';
	$Search_BodyRepeat_Simple_Member = 'Templates/Browse/T_Browse_BodyRepeat_Member.php';
	$Search_BodyFoot_Simple_Member = 'Templates/Browse/T_Browse_BodyFoot_Member.php';

	$Search_BodyHead_Simple_Group = 'Templates/Search/T_Search_BodyHead_Group.php';
	$Search_BodyRepeat_Simple_Group = 'Templates/Browse/T_Browse_BodyRepeat_Group.php';
	$Search_BodyFoot_Simple_Group = 'Templates/Browse/T_Browse_BodyFoot_Group.php';

//	$Search_BodyHead_Advanced = 'Templates/Browse/T_Browse_BodyHead_Group.php';
//	$Search_BodyRepeat_Advanced = 'Templates/Browse/T_Browse_BodyRepeat_Group.php';
//	$Search_BodyFoot_Advanced = 'Templates/Browse/T_Browse_BodyFoot_Group.php';

	$ArchiveButton = 'Templates/Common/T_Common_ArchiveButton.php';


	// Messages
	$BasicMessage = "Use '*' as a wildcard.";
	$NoResult = "No records matched your search term.";
	$SSError = "Your Search Term produced an error.";


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
	// Default for P is 1 which is the Simple search. Might want to change in future?
	$P_D = 1;
	$P_G = $_GET['P'];
	$P_S = $_SESSION['SessionP'];
	$P_P = $_POST['P'];
	$CurrentP = Validate_Vars($P_G, NULL, $P_P, $P_D);
	list($CurrentP, $ErrorArray) = CleanNumber($CurrentP, 'CurrentP', '', 2, 0, $ErrorArray);
	$_SESSION['SessionP'] = $CurrentP;

	// Validate the Current Simple Search term
	// $SS (Simple Search) comes from Post, $SS_D = default value
	$SS_D = '';
	$SS_G = $_GET['SS'];  // there should *NOT* be SS data input via GET.
	$SS_S = $_SESSION['SearchT'];
	$SS_P = $_POST['SS'];

	if (($CurrentP == 10) || ($CurrentP == 11))
	{
		$CurrentSS = Validate_Vars($SS_G, $SS_S, $SS_P, $SS_D);
		$_SESSION['SearchT'] = $CurrentSS;
	}
	else
	{$CurrentSS = '';}

	// L is the # of records to show at a time. Default = L_D
	$L_D = 10;
	$L_G = $_GET['L'];
	$L_S = $_SESSION['SessionL'];
	$L_P = $_POST['L'];

	if (($CurrentP == 10) || ($CurrentP == 11))
	{
		$CurrentL = Validate_Vars($L_G, $L_S, $L_P, $L_D);
		$_SESSION['SessionL'] = $CurrentL;
	}
	else
	{$CurrentL = $L_D;}
	list($CurrentL, $ErrorArray) = CleanNumber($CurrentL, 'CurrentL', '', 4, 0, $ErrorArray);


	// Validate the Current Sort
	// $Sort comes from URL, $Sort_D = default value (2)
	$Sort_D = 2;
	$Sort_G = $_GET['Sort'];
	$Sort_S = $_SESSION['Sort'];
	$Sort_P = $_POST['Sort'];
	if (($CurrentP == 10) || ($CurrentP == 11)) // basically, if we're coming from somewhere else, set Sort to default.
	{$CurrentSort = Validate_Vars($Sort_G, $Sort_S, $Sort_P, $Sort_D);}
	else {$CurrentSort = $Sort_D;}
	list($CurrentSort, $ErrorArray) = CleanNumber($CurrentSort, 'CurrentSort', '', 1, 1, $ErrorArray);
	$_SESSION['Sort'] = $CurrentSort;

	//Now check the ErrorArray and if there are errors, do an error routine
	$ErrorFlag = SetErrorArrayFlag($ErrorArray);
	if ($ErrorFlag == '') {$ErrorArray = '';}
	else
	{exit("<b>There was an error. Try again later. Sorry.</b>");}

	//With the other basic validations done, now do the Search term
	if($CurrentSS !='')
	{
		$CurrentSS = str_replace("%", "PCTSN", $CurrentSS);
		$CurrentSS = str_replace("*", "%", $CurrentSS);
		list($CurrentSS, $ErrorArray) = CleanTextString($CurrentSS, 'SS', 'Search String', 60, 1, $ErrorArray);
		$CurrentSS = stripslashes($CurrentSS); // this is due to magic quotes being on.

		if ($ErrorArray['E_SS'] !='')
		{$CurrentP = 3;}
	}


	// Set MorG defaults (radio button for nav bar)
	$MorG_M = '';
	$MorG_G = '';

//print("<br>Session:<pre>");
//print_r($_SESSION);
//print("</pre><br>");


	// Calculations
	//
	// records in the db are zero based so need to convert to that. (Z for Zero)
	$CurrentS_Z = CalcVar_Z($CurrentS);

	// Set ButtonArrays
	$ButtonArray[1] = array( '1', 'Search.php?P=1', 'Simple');
	$ButtonArray[2] = array( '2', 'Search.php?P=2', 'Advanced');


// do the appropriate SQL query and replace the appropriate fields in the Body section
// $P 1 & 2 are "main" pages using the base HTML template
//
//
	switch($CurrentP)
	{
		case 1 : // Simple Search Default

			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArray);

			// Build Page
			
			//Build Body
			$Body = FileInclude($Search_BodyDefault_Simple);
			$Message = $BasicMessage;
				
			// Do the replacements
			$output = str_replace("['Body']", $Body, $output);
			$output = str_replace("['NavBar']", '', $output);
			$output = str_replace("['MorG_M']", $MorG_M, $output);
			$output = str_replace("['MorG_G']", $MorG_G, $output);

		break;

		case 2 : // Advanced Search Default
		
			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArray);
			//Build Body
			$Body = FileInclude($Search_BodyDefault_Advanced);
				
			// Do the replacements
			$output = str_replace("['Body']", $Body, $output);
			$output = str_replace("['NavBar']", '', $output);

			// Will need to get to this...
		
		break;

		case 3 : // Error in Search String
			// Set the base template
			$output = Common_HNav_Page(1, $ButtonArray);
			$Message = $SSError;
			$Body = FileInclude($Search_BodyDefault_Simple);
		break;
		
		case 10: // Simple Search MEMBER results

			// Set MorG_M
			$MorG_M = 'checked';

			// Set the base template
			$output = Common_HNav_Page(1, $ButtonArray);

			// Set the Sort vars
			$SortArray[1] = 'Member.MemberID';
			$SortArray[2] = 'Member.LName';
			$SortArray[3] = 'Member.FName';
			$SortArray[4] = 'Member.DOB';
			$SortArray[5] = 'Member.Gender';
			$SortArray[6] = 'FamOrg.Name';
			$SortArray[7] = 'MemberType.MemberType';

			// Build Page
			
				// Need to count the number of rows
				$query =	"Member
							 LEFT JOIN FamOrg ON (Member.FamOrgID = FamOrg.FamOrgID)
							 LEFT JOIN MemberType ON (FamOrg.MemberTypeID = MemberType.MemberTypeID)
							 WHERE (Member.MemberID = '".$CurrentSS."') OR
							 (Member.Title LIKE '%".$CurrentSS."%') OR
							 (Member.Salutation LIKE '%".$CurrentSS."%') OR
							 (Member.PName LIKE '%".$CurrentSS."%') OR
							 (Member.FName LIKE '%".$CurrentSS."%') OR
							 (Member.LName LIKE '%".$CurrentSS."%') OR
							 (Member.Suffix LIKE '%".$CurrentSS."%') OR
							 (Member.DOB LIKE '%".$CurrentSS."%') OR
							 (Member.Gender LIKE '%".$CurrentSS."%') OR
							 (Member.MemberNotes LIKE '%".$CurrentSS."%')";

				$TotalRecords = CountRecords($query);

				// Do query and add in query results
				$QueryHead =	"SELECT Member.MemberID, Member.FamOrgID, Member.PName, Member.FName, Member.LName, Member.Suffix, Member.DOB, Member.Gender, FamOrg.Name, MemberType.MemberType FROM ";
				$QueryFoot = 	"ORDER BY ".$SortArray[$CurrentSort]." LIMIT ".$CurrentS_Z.",".$CurrentL;

				$query = $QueryHead.$query.$QueryFoot;

				$result = mysql_query($query);
				// error check
				if(!$result)
				{echo '<br />error - could not complete process the query. My deepest apologies.<br />';}

				if(mysql_num_rows($result) == 0)
				{
					$Message = $NoResult;
					$Body = FileInclude($Search_BodyDefault_Simple);
					break;
				}

				//Build Body
				$Body = FileInclude($Search_BodyHead_Simple_Member);

				// Set the Body Repeat file
				$BodyRepeatSection = FileInclude($Search_BodyRepeat_Simple_Member);

			// Assign variables to row data (variable name = field name
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
				$Body .= FileInclude($Search_BodyFoot_Simple_Member);
				
		break;
		
		case 11: // Simple Search GROUP results

			// Set MorG_G
			$MorG_G = 'checked';

			// Set the base template
			$output = Common_HNav_Page(1, $ButtonArray);

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


			// Do count query
			$query =	"FamOrg
							 LEFT JOIN MemberType ON (FamOrg.MemberTypeID = MemberType.MemberTypeID)
							 WHERE (FamOrg.FamOrgID = '".$CurrentSS."') OR
							 (FamOrg.Name LIKE '%".$CurrentSS."%') OR
							 (FamOrg.JoinDate LIKE '%".$CurrentSS."%') OR
							 (FamOrg.ExpDate LIKE '%".$CurrentSS."%') OR
							 (FamOrg.City LIKE '%".$CurrentSS."%') OR
							 (FamOrg.StateTwoLetter LIKE '%".$CurrentSS."%') OR
							 (FamOrg.Zip LIKE '%".$CurrentSS."%') OR
							 (MemberType.MemberType LIKE '%".$CurrentSS."%')";

			// Need to count the number of rows
			$TotalRecords = CountRecords($query);

			// Do query and add in query results
			$QueryHead = "SELECT FamOrg.FamOrgID, FamOrg.Name, FamOrg.JoinDate, FamOrg.ExpDate, FamOrg.City, FamOrg.StateTwoLetter, FamOrg.Zip, MemberType.MemberType FROM ";
			$QueryFoot = 	"ORDER BY ".$SortArray[$CurrentSort]." LIMIT ".$CurrentS_Z.",".$CurrentL;

			$query = $QueryHead.$query.$QueryFoot;

			$result = mysql_query($query);
			// error check
			if(!$result)
			{echo '<br />error - could not complete process the query. My deepest apologies.<br />';}

			if(mysql_num_rows($result) == 0)
			{
				$Message = $NoResult;
				$Body = FileInclude($Search_BodyDefault_Simple);
				break;
			}

			//Build Body
			$Body = FileInclude($Search_BodyHead_Simple_Group);

			// Set the Body Repeat files
			$BodyRepeatSection = FileInclude($Search_BodyRepeat_Simple_Group);
			$ArchiveButton = FileInclude($ArchiveButton);

			while($row = mysql_fetch_array($result))
			{
				foreach($row as $key => $val)
				{$$key = stripslashes($val);} 

				// CalcWarningFlag returns Red (Past), Yellow (Before end of next month) or None (Beyond end of next month) based on ExpDate. 
				$WarningFlag = CalcWarningFlag($ExpDate);

				//Convert Dates from MySQL format () to dd-mmm-yyyy format.
				$JoinDate = ConvertDateToDD_MMM_YYYY($JoinDate, 3);
				$ExpDate = ConvertDateToDD_MMM_YYYY($ExpDate, 3);

				// Convert Zip to 5+4 format
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
				$Body .= FileInclude($Search_BodyFoot_Simple_Group);
				
		break;
	}


// RecordNavBar stuff - needs further encapsulation (maybe an IF statement and a flag?) but this is a start...
//

	// Bring in the NavBar
	if ($TotalRecords > 10)
	{
		// Do RecordNavBar calcs and such
		$NavBar = RecordNavBar($NavBar, $NavBar_LeftDiv, $NavBar_CenterDiv, $NavBar_RightDiv, $CurrentS, $CurrentL, $TotalRecords, "Search.php");
	}
	else  // will have to change this when implement advanced search
	{
		$NavBar = FileInclude($NavBar);
		$NavBar_CenterDiv = FileInclude($NavBar_CenterDiv);

		$NavBar = str_replace("['NavBar_LeftDiv']", "&nbsp;", $NavBar);
		$NavBar = str_replace("['NavBar_CenterDiv']", $NavBar_CenterDiv, $NavBar);
		$NavBar = str_replace("['NavBar_RightDiv']", "&nbsp;", $NavBar);

		$NavBar = str_replace("['Previous']", $Previous, $NavBar);
		$NavBar = str_replace("['Next']", $Next, $NavBar);
		$NavBar = str_replace("['S_Plus_L_View']", $S_Plus_L_View, $NavBar);
	}

	// Need to strip slashes and convert the % back to % from the search term
	$CurrentSS = stripslashes($CurrentSS);
	$CurrentSS = str_replace("%", "*", $CurrentSS);
	$CurrentSS = str_replace("PCTSN", "%", $CurrentSS);

	// Now do the replacements to the Main page
	$output = str_replace( "['SectionTitleLeft']", 'Search', $output);
	$output = str_replace("['SectionSubjectLeft']", '', $output);
	$output = str_replace( "['SectionTitleRight']", '', $output);
	$output = str_replace("['SectionSubjectRight']", '', $output);
	$output = str_replace("['Body']", $Body, $output);
	$output = str_replace("['NavBar']", $NavBar, $output);
	$output = str_replace("['MorG_M']", $MorG_M, $output);
	$output = str_replace("['MorG_G']", $MorG_G, $output);
	$output = str_replace("['Message']", $Message, $output);
	$output = str_replace("['SS']", $CurrentSS, $output);
	$output = str_replace("['P']", $CurrentP, $output);
	$output = str_replace("['L']", $CurrentL, $output);
	$output = str_replace("['S']", $CurrentS, $output);

	// Sort
	//
	$SortCode = "<img src=\"Images/SortArrow.gif\" width=\"10\" height=\"10\" border=\"0\">";
	
	// Initiate SortViewArray and set the proper SortViewArray to the active state
		$SortViewArray = array();
		$SortViewArray[$CurrentSort] = $SortCode;

	// Replace the ['Sort?'] variables with the proper value
	for ($i = 1; $i<= count($SortArray); $i++)
	{
		$output = str_replace( "['Sort".$i."']", $SortViewArray[$i], $output);
	}		

	//Finally, replace error codes - only one right now is the SS - Search String.
	$output = ReplaceErrorCodes($output, $ErrorArray);


echo $output;
?>