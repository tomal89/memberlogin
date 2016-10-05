<?php
	session_start();

// print("Session:<pre>");
// print_r($_SESSION);
// print("</pre><br>");


	require('Includes/I_DBConnect.php');             // db connection stuff
	require('Includes/I_SetTableArrays.php');	 	 // Checks if various queries have been done previously. If so, they are stored in a $_SESSION var. If not, the query is done and the result stored in a $_SESSION var.
	require('Includes/I_SetErrorArrayFlag.php');	 // Checks the ErrorArray. If there are error codes, returns 1. Else returns 0.
	require('Includes/I_ReplaceErrorCodes.php');	 // Replaces error codes in the output with the appropriate error messages.
	require('Includes/I_DropDownFromArray.php');	 // Drop Down Select function from an array - might be able to replace I_Select.
	require('Includes/I_FamilyDropDownSelect.php');	 // FAMILY drop down select function - only used w/ the contact info pop up - might be able to move this to the I_Query_Common_PopWin_Contact.php file	
	require('Includes/I_FileInclude.php');           // file include function
	require('Includes/I_NameCombine.php');           // function to combines member name into a single string in a nice way - used when P=2	
	require('Includes/I_Query_FamOrg_NameOnly.php'); // function to replace ['Name'] with the current FamOrg name - used for all P except when P=1
	require('Includes/I_ConvertDate_1.php');		 // function to convert date format from (yyyy mm dd) TO (dd mmm yyyy) input must be a date in yyyy-mm-dd format.
	require('Includes/I_Convert_Zip.php');			 // Converts Zip from raw format to 5+4 format.
	require('Includes/I_WrapText.php');				 // function to wrap text
	require('Includes/I_LookUp.php');				 // function to find one field value from a table.
	require('Includes/I_Common_HNav_Page.php');	 	 // function to set horizontal menu in base page.
	require('Includes/I_CountRecords.php');	 		 // Counts rows in a table.
	require('Includes/I_Convert_DOB_to_Age.php');	 // Converts raw DOB (in the MySQL yyyy-mm-dd format) to age in years.
	require('Includes/I_UpdateExpDate.php');	 	 // Updates ExpDate by querying MemberType table & looking at dues rate.
	require('Includes/I_Validate_Vars.php');		 // Validates Various Vars. See script for values needed.
	require('Includes/I_Validations.php');			 // Mother of all validation includes. Functions for validating/cleaning ALL data.
	require('Includes/I_CalcVar_Z.php');	 		 // Caluculates a zero-based value for a var. (i.e. if X is 1, X_Z = 0).
	require('Includes/I_RecordNavBar.php');	 		 // Stuff for the RecordNavBar. Requires CalcVar_Z to work.
	require('Includes/I_CalcEndOfNextMonthUnix.php');// Stuff for the RecordNavBar. Requires CalcVar_Z to work.
	require('Includes/I_CalcWarningFlag.php');	 	 // Uses ExpDate to calc. a flag. Returns Red, Yellow or None. Requires CalcEndOfNextMonth to work.
	require('Includes/I_CalcSectionSubject.php');	 // Sets section subject to "Name (ARCHIVED)" if the FamOrgArchive flag = 1.
	require('Includes/I_ArchiveActivateGroup.php');	 // Simple function to archive/unarchive a group.


	require('Includes/I_Query_FamOrg_New.php');
	require('Includes/I_Query_FamOrg_Info.php');
	require('Includes/I_Query_FamOrg_Members.php');
	require('Includes/I_Query_FamOrg_ContactInfo.php');
	require('Includes/I_Query_FamOrg_Dues.php');
	require('Includes/I_Query_FamOrg_VolunteerInfo.php');
	require('Includes/I_Query_FamOrg_PopWin_Volunteer.php');
	require('Includes/I_Query_FamOrg_DeleteVolunteer.php');


	require('Includes/I_Query_FamOrg_PopWin_Dues.php');
	require('Includes/I_Query_Common_PopWin_Contact.php');
	require('Includes/I_Query_Common_DeleteContactInfo.php'); // function for deleting a single row from the contact table by ContactID


	// Set the various templates here
	$NavBar = 'Templates/Common/T_Common_NavBar.php';
	$NavBar_LeftDiv = 'Templates/Common/T_Common_NavBar_LeftDiv.php';
	$NavBar_CenterDiv = '';
	$NavBar_RightDiv = 'Templates/Common/T_Common_NavBar_RightDiv.php';

	$T_Info_Body = 'Templates/FamOrg/T_FamOrg_Info.php';
	$T_Info_BodyFoot = 'Templates/FamOrg/T_FamOrg_InfoFoot.php';

	$T_New_BodyHead = 'Templates/FamOrg/T_FamOrg_New_BodyHead.php';
	$T_New_BodyMain = 'Templates/FamOrg/T_FamOrg_PopWin_Info.php';
	$T_New_BodyFoot = 'Templates/FamOrg/T_FamOrg_New_BodyFoot.php';

	$T_PopWin_Result = 'Templates/T_Common_PopWin_Result.php';
	
	$ArchiveButton = 'Templates/Common/T_Common_ArchiveButton.php';
	$ActivateButton = 'Templates/Common/T_Common_ActivateButton.php'; // these two buttons can be combined into one with some logic.


	// Validate the Current A (Archive Flag)
	// $A comes from URL, $A_D = default value (0)
	$A_D = 0;
	$A_G = $_GET['A'];
	$A_S = '';
	$A_P = $_POST['A'];
	$CurrentA = Validate_Vars($A_G, '', $A_P, $A_D);
	list($CurrentA, $ErrorArray) = CleanNumber($CurrentA, 'CurrentA', 'Current Archive Flag', 1, 1, $ErrorArray);

	// Figure out where the CurrentFamOrgID is coming from and validate it
	$F_D = ''; // Need some sort of error processor for if this happens.
	$F_S = $_SESSION['SessionF'];
	$F_P = $_POST['F'];
	$F_G = $_GET['F'];
	$CurrentFamOrgID = Validate_Vars($F_G, $F_S, $F_P, $F_D);
	if($CurrentFamOrgID) {list($CurrentFamOrgID, $ErrorArray) = CleanNumber($CurrentFamOrgID, 'CurrentFamOrgID', '', 4, 0, $ErrorArray);}
	$_SESSION['SessionF'] = $CurrentFamOrgID;

	// Validate the Current P (Page)
	$P_D = 1;
	$P_S = '';
	$P_P = $_POST['P'];
	$P_G = $_GET['P'];
	$CurrentP = Validate_Vars($P_G, $P_S, $P_P, $P_D);
	if ($CurrentP !='New'){list($CurrentP, $ErrorArray) = CleanNumber($CurrentP, 'CurrentP', '', 2, 0, $ErrorArray);}

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

	// Validate the Current L (# of records to show)
	// $L comes from URL, $L_D = default value (20)
	$L_D = 10;
	$L_G = $_GET['L'];
	$L_S = $_SESSION['SessionL'];
	$L_P = $_POST['L'];
	if ((($P_G) || ($P_P)) && ($CurrentP == $P_S))
	{
		$CurrentL = Validate_Vars($L_G, $L_S, $L_P, $L_D);
		$_SESSION['SessionL'] = $CurrentL;
	}
	else
	{$CurrentL = $L_D;}
	list($CurrentL, $ErrorArray) = CleanNumber($CurrentL, 'CurrentL', '', 4, 0, $ErrorArray);

	// Contact ID (C)
	$C_D = '';
	$C_S = '';
	$C_P = $_POST['C'];
	$C_G = $_GET['C'];
	$CurrentContactID = Validate_Vars($C_G, $C_S, $C_P, $C_D);
	if(($CurrentContactID)&&($CurrentContactID!='New')){list($CurrentContactID, $ErrorArray) = CleanNumber($CurrentContactID, 'CurrentContactID', '', 5, 1, $ErrorArray);}
	$_SESSION['C'] = $CurrentContactID;

	// Dues ID (D)
	$D_D = '';
	$D_S = $_SESSION['D'];
	$D_P = $_POST['D'];
	$D_G = $_GET['D'];
	$CurrentDuesID = Validate_Vars($D_G, $D_S, $D_P, $D_D);
	if(($CurrentDuesID)&&($CurrentDuesID!='New')){list($CurrentDuesID, $ErrorArray) = CleanNumber($CurrentDuesID, 'CurrentDuesID', '', 5, 1, $ErrorArray);}
	$_SESSION['D'] = $CurrentDuesID;

	$V_D = '';
	$V_S = $_SESSION['V'];
	$V_P = $_POST['V'];
	$V_G = $_GET['V'];
	$CurrentVolTypeID = Validate_Vars($V_G, $V_S, $V_P, $V_D);
	if(($CurrentVolTypeID) && ($CurrentVolTypeID !='New')){list($CurrentVolTypeID, $ErrorArray) = CleanNumber($CurrentVolTypeID, 'CurrentVolTypeID', '', 3, 1, $ErrorArray);}
	$_SESSION['V'] = $CurrentVolTypeID;


	//Now check the ErrorArray and if there are errors, do an error routine
	$ErrorFlag = SetErrorArrayFlag($ErrorArray);
	if ($ErrorFlag == '') {$ErrorArray = '';}
	else
	{exit("<b>There was an error. Try again later. Sorry.</b>");}


	// Calculations
	//
	// records in the db are zero based so need to convert to that. (Z for Zero)
	$CurrentS_Z = CalcVar_Z($CurrentS);


	// Archive/Activate
	
	// If Archive Flag is present (A=2), activate the current FamOrgID and then process the page (P)
	// A=2 is used b/c zero can cause problems. But the db uses '0' as active and '1' as inactive.
	if ($CurrentA == 2)
		{
			$result = ArchiveActivateGroup(0, $CurrentFamOrgID);
			// error check
			if($result == "False")
			{exit("<br /><b>ERROR - could not activate the Group.<br />Perhaps it is because of a misalignment in the stars.<br />My deepest apologies.<br />");}
		}

	// If Archive Flag is present (A=1), archive the current FamOrgID and then process the page (P)
	if ($CurrentA == 1)
		{
			$result = ArchiveActivateGroup(1, $CurrentFamOrgID);
			// error check
			if($result == "False")
			{exit("<br /><b>ERROR - could not archive the Group.<br />Perhaps it is because of a misalignment in the stars.<br />My deepest apologies.<br />");}
		}


	// Set ButtonArrays
	$ButtonArray[1] = array( '1', 'FamOrg.php?P=1', 'Info');
	$ButtonArray[2] = array( '2', 'FamOrg.php?P=2', 'Members');
	$ButtonArray[3] = array( '3', 'FamOrg.php?P=3', 'Contact Info');
	$ButtonArray[4] = array( '4', 'FamOrg.php?P=4', 'Dues');
	$ButtonArray[5] = array( '5', 'FamOrg.php?P=5', 'Volunteer');
	
	//create and set values for ButtonArrayNew (used for new FamOrg)
	$ButtonArrayNew = $ButtonArray;	
	for ($i = 1; $i < (count($ButtonArrayNew)+1); $i++)
	{
	$ButtonArrayNew[$i][1] = '#';
	}
	$ButtonArrayNew[1][0] = 'New';



function FamOrgPopWin($CurrentP)
{
	// Bring in the base template
	$output = FileInclude('Templates/T_Common_PopWin.php');
	// Set this as a FamOrg page (not Member)
	$output = str_replace("['FormAction']", 'FamOrg.php', $output);	
	
	switch($CurrentP)
	{
	case 10 : // Info
			//Get the body template, set the page title and success P number
			$BodyView = FileInclude('Templates/FamOrg/T_FamOrg_PopWin_Info.php');
			$PageTitle = "Group Edit";
			$PNum = 20;
	break;
	case 11 : // Dues
			//Get the body template, set the page title and success P number
			$BodyView = FileInclude('Templates/FamOrg/T_FamOrg_PopWin_Dues.php');
			$PageTitle = "Dues Pay/Edit";
			$PNum = 21;
	break;
	case 12 : // Contact
			//Get the body template, set the page title and success P number
			$BodyView = FileInclude('Templates/T_Common_PopWin_Contact.php');
			$PageTitle = "Contact Add/Edit";
			$PNum = 22;
	break;
	case 13 : // Volunteer
			//Get the body template, set the page title and success P number
			$BodyView = FileInclude('Templates/FamOrg/T_FamOrg_PopWin_Volunteer.php');
			$PageTitle = "Volunteer Edit";
			$PNum = 21;
	break;

	}
	
	// Replace body with page specific
	$output = str_replace("['Body']", $BodyView, $output);
	// Replace PageTitle with something appropriate
	$output = str_replace( "['PageTitle']", $PageTitle, $output);
	//Replace ['P'] in the output with the return value
	$output = str_replace( "['P']", $PNum, $output);

	return $output;
}


// do the appropriate SQL query and replace the appropriate fields in the Body section
// $P 1 to 4 are "main" pages using the base HTML template
// $P 10 & 11 are "PopUpWin" pages for editing/deleting
// $P 20 & 21 are the returns from $P 10 & 11 (do the updates to the db)

	switch($CurrentP)
	{
		case 'New' : // New group
			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArrayNew);
			$output = str_replace( "['SectionTitleLeft']", 'Group:', $output);
			$output = str_replace( "['SectionTitleRight']", 'Group ID:', $output);
			//Build body
			$Body = FileInclude($T_New_BodyHead);
			$Body .= FileInclude($T_New_BodyMain);
			$Body .= FileInclude($T_New_BodyFoot);
			//Replace ['Body'] in the output with the template
			$output = str_replace( "['Body']", $Body, $output);
			//Do the query and replace the ['xxx'] fields in the whole doc
			// Now set CurrentP for the return page
			$CurrentP = 14;
			$output = FamOrg_New($output, $CurrentP, '', '');
			break;
		case 1 : // Info page
			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArray);
			$output = str_replace( "['SectionTitleLeft']", 'Group:', $output);
			$output = str_replace( "['SectionTitleRight']", 'Group ID:', $output);
			//Set $BodyView to the apprpriate template
			$Body = FileInclude($T_Info_Body);
			$Body .= FileInclude($T_Info_BodyFoot);
			//Replace ['Body'] in the output with the template
			$output = str_replace( "['Body']", $Body, $output);
			//Do the query and replace the ['xxx'] fields in the whole doc
			$output = FamOrg_Info($output, $CurrentFamOrgID, '', '');
			break;
		case 2 : // Member list page
			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArray);
			$output = str_replace( "['SectionTitleLeft']", 'Group:', $output);
			$output = str_replace( "['SectionTitleRight']", 'Group ID:', $output);
			//Run the member list function...
			list($output, $TotalRecords) = FamOrg_Members($output, $CurrentFamOrgID, $CurrentS_Z, $CurrentL);
			//Replace ['Name'] with the CurrentFamOrg name
			$output = FamOrg_NameOnly($output, $CurrentFamOrgID);
			//Replace ['FamOrgID'] with the current one
			$output = str_replace( "['CurrentFamOrgID']", $CurrentFamOrgID, $output);
			break;
		case 3 : // Contact info page
			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArray);
			$output = str_replace( "['SectionTitleLeft']", 'Group:', $output);
			$output = str_replace( "['SectionTitleRight']", 'Group ID:', $output);
			//Run the contact info list function...
			list($output, $TotalRecords) = FamOrg_ContactInfo($output, $CurrentFamOrgID, $CurrentS_Z, $CurrentL);
			//Replace ['Name'] with the CurrentFamOrg name
			$output = FamOrg_NameOnly($output, $CurrentFamOrgID);
			break;
		case 4 : // Dues page
			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArray);
			$output = str_replace( "['SectionTitleLeft']", 'Group:', $output);
			$output = str_replace( "['SectionTitleRight']", 'Group ID:', $output);
			//Run the Dues list function...
			list($output, $TotalRecords) = FamOrg_Dues($output, $CurrentFamOrgID, $CurrentS_Z, $CurrentL);
			//Replace ['Name'] with the CurrentFamOrg name
			$output = FamOrg_NameOnly($output, $CurrentFamOrgID);
			break;
		case 5 : // Volunteer List
			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArray);
			$output = str_replace( "['SectionTitleLeft']", 'Group:', $output);
			$output = str_replace( "['SectionTitleRight']", 'Group ID:', $output);
			//Run the volunteer info list function...
			$output = FamOrg_VolunteerList($output, $CurrentFamOrgID);
			//Replace ['Name'] with the CurrentFamOrg name
			$output = FamOrg_NameOnly($output, $CurrentFamOrgID);
			$_SESSION['V'] = '';
			$V = '';
			break;

		case 10 : // PopUp Edit Info page
			// Construct the page
			$output = FamOrgPopWin($CurrentP);
			//Do the query and replace the ['xxx'] fields in the whole doc
			$output = FamOrg_Info($output, $CurrentFamOrgID, '', '');
			break;
		case 11 : // PopUp Add/Edit Dues page
			// Construct the page
			$output = FamOrgPopWin($CurrentP);
			//Do the query and replace the ['xxx'] fields in the whole doc
			$output = FamOrg_PopWin_Dues($output, $CurrentFamOrgID, $CurrentDuesID, '', '');
			// clean up variables
			$_SESSION['D'] = '';
			break;
		case 12 : // PopUp Add/Edit Contact page
			// Construct the page
			$output = FamOrgPopWin($CurrentP);
			//Do the query and replace the ['xxx'] fields in the whole doc
			$output = Common_PopWin_Contact($output, $CurrentContactID, $CurrentFamOrgID, $CurrentMemberID, '', '');
			// clear the variables
			$_SESSION['C'] = '';
			break;
		case 13 : // Delete Contact page
			// This case # must be the same as in Member.
			//Run the contact info list function...
			$result = Common_DeleteContactInfo($CurrentContactID);
			if(!$result)
			{
				echo "There was a problem. I was unable to delete the record. Sorry!";
				break;
			}
			// Now do the same thing as case 3 - maybe make case 3 into a function?
			//Set P to 3 for Info
			$CurrentP = 3;
			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArray);
			$output = str_replace( "['SectionTitleLeft']", 'Group:', $output);
			$output = str_replace( "['SectionTitleRight']", 'Group ID:', $output);
			//Run the contact info list function...
			list($output, $TotalRecords) = FamOrg_ContactInfo($output, $CurrentFamOrgID, $CurrentS_Z, $CurrentL);
			//Replace ['Name'] with the CurrentFamOrg name
			$output = FamOrg_NameOnly($output, $CurrentFamOrgID);
			// Clear uneeded variables
			$_SESSION['C'] = '';;
			$_SESSION['M'] = '';;
			break;
		case 14 : // Process New Group (return from 'New')

				foreach($_POST as $key => $val)
				{$$key = $val;} 

				// Clean the variables
				list($MemberTypeID, $ErrorArray) = CleanType($MemberType, 'MemberType', 'Member Type', 'MemberTypeArray', $ErrorArray);
				list($Name, $ErrorArray) = CleanTextString($Name, 'Name', 'Name', 20, 0, $ErrorArray);
				list($JoinDay, $JoinMonth, $JoinYear, $ErrorArray) = CleanDate($JoinDay, $JoinMonth, $JoinYear, 'JoinDate', 'JoinDay', 'JoinMonth', 'JoinYear', 'Join Day', 'Join Month', 'Join Year', 'Join Date', 0, $ErrorArray);
				list($Directory, $ErrorArray) = CleanTwoChoice($Directory, 'Directory', 'Directory', 'Y', 'N', 'Yes', 'No', $ErrorArray);
				list($Street1, $ErrorArray) = CleanTextString($Street1, 'Street1', 'Street Line 1', 50, 1, $ErrorArray);
				list($Street2, $ErrorArray) = CleanTextString($Street2, 'Street2', 'Street Line 2', 50, 1, $ErrorArray);
				list($Street3, $ErrorArray) = CleanTextString($Street3, 'Street3', 'Street Line 3', 50, 1, $ErrorArray);
				list($City, $ErrorArray) = CleanTextString($City, 'City', 'City', 20, 1, $ErrorArray);
				list($Zip5, $Zip4, $ErrorArray) = CleanZip($Zip5, $Zip4, 'Zip5', 'Zip4', 1, $ErrorArray);
				list($StateTwoLetter, $Region, $Country, $ErrorArray) = CleanStateRegionCountry($StateTwoLetter, $Region, $Country, 'StateTwoLetter', 'Region', 'Country', $ErrorArray);
				list($FamOrgNotes, $ErrorArray) = CleanTextString($FamOrgNotes, 'FamOrgNotes', 'Group Notes', 1000, 1, $ErrorArray);

				// See if there are any errors
				$ErrorArrayFlag = SetErrorArrayFlag($ErrorArray);

				if ($ErrorArrayFlag == 1)
				{
					// Set the base template
					$output = Common_HNav_Page($CurrentP, $ButtonArrayNew);
					$output = str_replace("['SectionTitleLeft']", 'Group:', $output);
					//Build body
					$Body = FileInclude($T_New_BodyHead);
					$Body .= FileInclude($T_New_BodyMain);
					$Body .= FileInclude($T_New_BodyFoot);
					//Replace ['Body'] in the output with the template
					$output = str_replace( "['Body']", $Body, $output);

					$InfoArray['MemberTypeID'] = $MemberTypeID;
					$InfoArray['Name'] = stripslashes($Name);
					$InfoArray['JoinYear'] = $JoinYear;
					$InfoArray['JoinMonth'] = $JoinMonth;
					$InfoArray['JoinDay'] = $JoinDay;
					$InfoArray['Directory'] = $Directory;
					$InfoArray['BadAddress'] = '0';
					$InfoArray['Street1'] = stripslashes($Street1);
					$InfoArray['Street2'] = stripslashes($Street2);
					$InfoArray['Street3'] = stripslashes($Street3);
					$InfoArray['City'] = stripslashes($City);
					$InfoArray['StateTwoLetter'] = $StateTwoLetter;
					$InfoArray['Zip5'] = stripslashes($Zip5);
					$InfoArray['Zip4'] = stripslashes($Zip4);
					$InfoArray['Region'] = stripslashes($Region);
					$InfoArray['Country'] = stripslashes($Country);
					$InfoArray['FamOrgNotes'] = stripslashes($FamOrgNotes);

					//Do the query and replace the ['xxx'] fields in the whole doc
					$output = FamOrg_New($output, 14, $InfoArray, $ErrorArray);
					break;
				}
				if ($ErrorArrayFlag != 1)
				{
				// Do calcs
				$JoinDate = $JoinYear.'-'.$JoinMonth.'-'.$JoinDay;				
				$Zip = $Zip5.$Zip4;

				// Set expiration date to zero b/c there has been no payment yet.
				$ExpDate = '0000-00-00';

				// Translate Directory
				if ($Directory == 'Y')
				{$Directory = 0;}
				else {$Directory = 1;}

				// Process Country
				if (!$Country)
				{$Country = 'USA';}

				// Set and do the query
				$query =	"INSERT INTO FamOrg
							SET MemberTypeID = '$MemberTypeID',
							Name = '$Name',
							JoinDate = '$JoinDate',
							ExpDate = '$ExpDate',
							Directory = '$Directory',
							FamOrgArchive = '0',
							BadAddress = '0',
							Street1 = '$Street1',
							Street2 = '$Street2',
							Street3 = '$Street3',
							City = '$City',
							StateTwoLetter = '$StateTwoLetter',
							Zip = '$Zip',
							Region = '$Region',
							Country = '$Country',
							FamOrgNotes = '$FamOrgNotes'";

				// actually do the query
				$result = mysql_query($query);
				// error check
				if(!$result)
				{echo '<br />error - could not complete INSERT<br />';}
				else
				{
					// Set CurrentP to 1, Determine the new records FamOrgID and do the same thing as for 1					
					$CurrentP = 1;

					// Determine new record's FamOrgID
					$CurrentFamOrgID = mysql_insert_id();
					$_SESSION['SessionF'] = $CurrentFamOrgID; // Need to set it or there are potential sync problems on reload

					// Set the base template
					$output = Common_HNav_Page($CurrentP, $ButtonArray);
					$output = str_replace( "['SectionTitleLeft']", 'Group:', $output);
					$output = str_replace( "['SectionTitleRight']", 'Group ID:', $output);
					//Set $BodyView to the appropriate template
					$Body = FileInclude($T_Info_Body);
					$Body .= FileInclude($T_Info_BodyFoot);
					//Replace ['Body'] in the output with the template
					$output = str_replace( "['Body']", $Body, $output);
					//Do the query and replace the ['xxx'] fields in the whole doc
					$output = FamOrg_Info($output, $CurrentFamOrgID, $InfoArray, $ErrorArray);
				}
		}
		break;
		case 15 : // PopUp Add/Edit Volunteer page
			// Construct the page
			$output = FamOrgPopWin(13);
			//Do the query and replace the ['xxx'] fields in the whole doc
			$output = FamOrg_PopWin_Volunteer($output, $CurrentFamOrgID, $CurrentVolTypeID, '', '');
			$_SESSION['V'] = '';
			break;
		case 16 : // Delete Volunteer
			//Run the contact info list function...
			$result = Group_DeleteVolunteer($CurrentVolTypeID, $CurrentFamOrgID);
			if(!$result)
			{
				echo "There was a problem. I was unable to delete the record. Sorry!";
				break;
			}
			// Now do the same thing as case 4 - maybe make case 4 into a function?
			//Set P to 4 for Volunteer
			$CurrentP = 5;
			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArray);
			$output = str_replace( "['SectionTitleLeft']", 'Group:', $output);
			$output = str_replace( "['SectionTitleRight']", 'Group ID:', $output);
			//Run the volunteer info list function...
			$output = FamOrg_VolunteerList($output, $CurrentFamOrgID);
			//Replace ['Name'] with the CurrentFamOrg name
			$output = FamOrg_NameOnly($output, $CurrentFamOrgID);
			// Clear uneeded variables
			$_SESSION['V'] = '';
			break;


		case 20 : // Return from PopUp Edit Info page
				// Process form inputs
				foreach($_POST as $key => $val)
				{$$key = $val;} 

				// Clean & Validate vars
				list($MemberTypeID, $ErrorArray) = CleanType($MemberTypeID, 'MemberType', 'Member Type', 'MemberTypeArray', $ErrorArray);
				list($Name, $ErrorArray) = CleanTextString($Name, 'Name', 'Group Name', 50, 0, $ErrorArray);
				list($JoinDay, $JoinMonth, $JoinYear, $ErrorArray) = CleanDate($JoinDay, $JoinMonth, $JoinYear, 'JoinDate', 'JoinDay', 'JoinMonth', 'JoinYear', 'Join Day', 'Join Month', 'Join Year', 'Join Date', 1, $ErrorArray);
				list($Directory, $ErrorArray) = CleanTwoChoice($Directory, 'Directory', 'Directory', 'Y', 'N', 'Yes', 'No', $ErrorArray);
				list($BadAddress, $ErrorArray) = CleanTwoChoice($BadAddress, 'BadAddress', 'Bad Address', 'Y', 'N', 'Yes', 'No', $ErrorArray);
				list($Street1, $ErrorArray) = CleanTextString($Street1, 'Street1', 'Street Line 1', 50, 1, $ErrorArray);
				list($Street2, $ErrorArray) = CleanTextString($Street2, 'Street2', 'Street Line 2', 50, 1, $ErrorArray);
				list($Street3, $ErrorArray) = CleanTextString($Street3, 'Street3', 'Street Line 3', 50, 1, $ErrorArray);
				list($City, $ErrorArray) = CleanTextString($City, 'City', 'City', 20, 1, $ErrorArray);
				list($Zip5, $Zip4, $ErrorArray) = CleanZip($Zip5, $Zip4, 'Zip5', 'Zip4', 1, $ErrorArray);
				list($StateTwoLetter, $Region, $Country, $ErrorArray) = CleanStateRegionCountry($StateTwoLetter, $Region, $Country, 'StateTwoLetter', 'Region', 'Country', $ErrorArray);
				list($FamOrgNotes, $ErrorArray) = CleanTextString($FamOrgNotes, 'FamOrgNotes', 'Group Notes', 1000, 1, $ErrorArray);

				// See if there are any errors
				$ErrorArrayFlag = SetErrorArrayFlag($ErrorArray);

				if ($ErrorArrayFlag == 1)
				{
					// Create the FamOrg_InfoArray to return the values to the form
					$InfoArray['MemberTypeID'] = $MemberTypeID;
					$InfoArray['Name'] = stripslashes($Name);
					$InfoArray['JoinYear'] = $JoinYear;
					$InfoArray['JoinMonth'] = $JoinMonth;
					$InfoArray['JoinDay'] = $JoinDay;
					$InfoArray['Directory'] = $Directory;
					$InfoArray['BadAddress'] = $BadAddress;
					$InfoArray['Street1'] = stripslashes($Street1);
					$InfoArray['Street2'] = stripslashes($Street2);
					$InfoArray['Street3'] = stripslashes($Street3);
					$InfoArray['City'] = stripslashes($City);
					$InfoArray['StateTwoLetter'] = $StateTwoLetter;
					$InfoArray['Zip5'] = stripslashes($Zip5);
					$InfoArray['Zip4'] = stripslashes($Zip4);
					$InfoArray['Region'] = stripslashes($Region);
					$InfoArray['Country'] = stripslashes($Country);
					$InfoArray['FamOrgNotes'] = stripslashes($FamOrgNotes);

					// Construct the page
					$CurrentP = 10;
					$output = FamOrgPopWin($CurrentP);
					//Do the query and replace the ['xxx'] fields in the whole doc
					$output = FamOrg_Info($output, $CurrentFamOrgID, $InfoArray, $ErrorArray);
					break;
				}
				if ($ErrorArrayFlag != 1)
				{
					
					// get info to see what's changed - right now is only join date. might want to do more later.
					$query = "SELECT MemberTypeID, JoinDate, ExpDate FROM FamOrg WHERE FamOrgID = '$CurrentFamOrgID' LIMIT 1";
					$result = mysql_query($query);
					if(!$result)
					{echo '<br />error - could not complete SELECT JoinDate<br />';}
					else
					{
						$row = mysql_fetch_array($result);
						$MTID2 = stripslashes($row['MemberTypeID']);
						$JD2 = stripslashes($row['JoinDate']);
						$ED2 = stripslashes($row['ExpDate']);
						$JD2Array = explode("-", $JD2);
						$ED2Array = explode("-", $ED2);
					}
					
					// if JoinDate has changed, need to recalc month/day of ExpDate
					if(($JD2Array[1] != $JoinMonth) || ($JD2Array[2] != $JoinDay))
					{$ExpDate = $ED2Array[0]."-".$JoinMonth."-".$JoinDay;}
					else
					{$ExpDate = $ED2;}

					// if MemberType has changed, need to recalc ExpDate
					if($MTID2 != $MemberTypeID)
					{
						if($MemberTypeID > 3) // this is a quick and dirty fix - need to do better.
						{$ExpDate = UpdateExpDate($MemberTypeID, $ExpDate);}
						elseif($ExpDate == '' || $ExpDate == '0000-00-00') // this is a quick and dirty fix - need to do better.
						{$ExpDate = date("Y")."-".$JoinMonth."-".$JoinDay;}
					}

					// Translate Directory
					if ($Directory == 'Y')
					{$Directory = 0;}
					else {$Directory = 1;}

					// Translate BadAddress
					if ($BadAddress == 'N')
					{$BadAddress = 0;}
					else {$BadAddress = 1;}

					// Process JoinDate & Zip to fit the MySQL format
					$Zip = $Zip5.$Zip4;
					$JoinDate = $JoinYear.'-'.$JoinMonth.'-'.$JoinDay;
/*					if($JoinDate && ($JoinDate != $JoinDate2) && ($JoinDate != '0000-00-00'))
					{
						$ExpDate = UpdateExpDate($MemberTypeID, $JoinDate); // changing the MemberType (MemberTypeID) will possibly change the ExpDate.
						$ExpDateStr = "JoinDate = '".$JoinDate."', ExpDate = '".$ExpDate."',";
					}
*/

					// stuff for update
					$query =	"UPDATE FamOrg
								SET MemberTypeID = '".$MemberTypeID."',
									Name = '".$Name."',
									JoinDate = '".$JoinDate."',
									ExpDate = '".$ExpDate."',
									Directory = '".$Directory."',
									BadAddress = '".$BadAddress."',
									Street1 = '".$Street1."',
									Street2 = '".$Street2."',
									Street3 = '".$Street3."',
									City = '".$City."',
									StateTwoLetter = '".$StateTwoLetter."',
									Zip = '".$Zip."',
									Region = '".$Region."',
									Country = '".$Country."',
									FamOrgNotes ='".$FamOrgNotes."'
								WHERE FamOrgID = '".$CurrentFamOrgID."'";

					// actually do the query
					$result = mysql_query($query);
					// error check
					if(!$result)
					{echo '<br />error - could not complete UPDATE<br />';}
					else
					{
						$output = FileInclude($T_PopWin_Result);
						$output = str_replace( "['PageTitle']", "Group Update Result", $output);
						$_SESSION['SessionP'] = 1;
					}
				}
			break;
			case 21 : // Return from PopUp Edit Dues page
				// Process form inputs
				foreach($_POST as $key => $val)
				{$$key = $val;
//				echo "<br />".$key." => ".$val;
				}

				// Clean & Validate vars
				list($CurrentDuesID, $ErrorArray) = CleanIDNumber($D, 'DuesID', 'Dues ID', $ErrorArray);
				list($MemberTypeID, $ErrorArray) = CleanType($MTID, 'MemberType', 'Member Type', 'MemberTypeArray', $ErrorArray);
				list($AmtDue, $ErrorArray) = CleanCurrency($AD, 'AmtDue', 'Amount Due', 6, 0, $ErrorArray);
				list($DuesDueDate, $ErrorArray) = CleanDate2($DDD, 'DuesDueDate', 'Dues Due Date', $ErrorArray);
				list($AmtPaid, $ErrorArray) = CleanCurrency($AmtPaid, 'AmtPaid', 'Amount Paid', 6, 0, $ErrorArray);
				list($PaidDay, $PaidMonth, $PaidYear, $ErrorArray) = CleanDate($PaidDay, $PaidMonth, $PaidYear, 'Paid Date', 'PaidDay', 'PaidMonth', 'PaidYear', 'Paid Day', 'Paid Month', 'Paid Year', 'Paid Date', 1, $ErrorArray);
				list($DuesNotes, $ErrorArray) = CleanTextString($DuesNotes, 'DuesNotes', 'Dues Notes', 1000, 1, $ErrorArray);
				list($ExpDate, $ErrorArray) = CleanDate2($ExpDate, 'ExpDate', 'Expiration Date', $ErrorArray);

				// See if there are any errors
				$ErrorArrayFlag = SetErrorArrayFlag($ErrorArray);

				if ($ErrorArrayFlag == 1)
				{	
					$PaidDate = $PaidYear."-".$PaidMonth."-".$PaidDay;

					// Set the InfoArray vars to current for passing back to the form
					$InfoArray['MemberTypeID'] = $MemberTypeID;
					$InfoArray['AmtDue'] = $AmtDue;
					$InfoArray['DuesDueDate'] = $DuesDueDate;
					$InfoArray['AmtPaid'] = $AmtPaid;
					$InfoArray['PaidDate'] = $PaidDate;
					$InfoArray['DuesNotes'] = stripslashes($DuesNotes);
					$InfoArray['ExpDate'] = $ExpDate;

					// Construct the page
					$output = FamOrgPopWin(11);
					//Do the query and replace the ['xxx'] fields in the whole doc
					$output = FamOrg_PopWin_Dues($output, $CurrentFamOrgID, $CurrentDuesID, $InfoArray, $ErrorArray);
					// now the error codes
					$output = ReplaceErrorCodes($output, $ErrorArray);
					// clean up variables
					$_SESSION['D'] = '';
					break;
				}
				else
				{										
					// Process JoinDate & Zip to fit the MySQL format
					$PaidDate = $PaidYear.'-'.$PaidMonth.'-'.$PaidDay;
				
					// determine if this is an update or insert
					if ($CurrentDuesID == 'New')
					{
						$query =	"INSERT INTO Dues
									SET FamOrgID = '$CurrentFamOrgID',
									MemberTypeID = '$MemberTypeID',
									AmtDue = '$AmtDue',
									DuesDueDate = '$DuesDueDate',
									AmtPaid = '$AmtPaid',
									PaidDate = '$PaidDate',
									DuesNotes = '$DuesNotes'";

						// Need to calc new ExpDate and MemberTypeID
						$ExpDate = UpdateExpDate($CurrentFamOrgID, $MemberTypeID, $ExpDate); // changing the MemberType (MemberTypeID) will possibly change the ExpDate.

						$query2 = "UPDATE FamOrg
									SET ExpDate = '$ExpDate',
									MemberTypeID = '$MemberTypeID'
									WHERE FamOrgID = '$CurrentFamOrgID'";
					}
					else
					{
						// stuff for update
						$query =	"UPDATE Dues
									SET MemberTypeID = '$MemberTypeID',
									AmtDue = '$AmtDue',
									AmtPaid = '$AmtPaid',
									PaidDate = '$PaidDate',								
									DuesNotes ='$DuesNotes'
									WHERE DuesID = '$CurrentDuesID'";
					}
					// actually do the query
					$result = mysql_query($query);
					// error check
					if(!$result)
					{
						echo '<br />error - could not complete INSERT/UPDATE<br />';
						break;
					}
					if($query2)
					{
						$result = mysql_query($query2);
						if(!$result)
						{
							echo '<br />error - could not complete FAMORG UPDATE<br />';
							break;
						}
					}			
					$output = FileInclude($T_PopWin_Result);
					$output = str_replace( "['PageTitle']", "Dues Add/Update Result", $output);
					// Destroy session var
					$_SESSION['D'] = '';
				}
			break;
			case 22 : // Return from PopUp Contact Info page
			// Process form inputs

				foreach($_POST as $key => $val)
				{$$key = $val;
//				echo "<br />".$key." => ".$val;
				}

				// Clean & Validate vars
				list($Name, $ErrorArray) = CleanTextString($GN, 'Name', 'Group Name', 30, 0, $ErrorArray);
				list($ContactTypeID, $ErrorArray) = CleanType($ContactTypeDropDown, 'ContactType', 'Contact Type', 'ContactTypeArray', $ErrorArray);
				list($CurrentMemberID, $ErrorArray) = CleanIDNumber($MemberID, 'MemberID', 'Member Name', $ErrorArray);
				list($ContactInfo, $ErrorArray) = CleanTextString($ContactInfo, 'ContactInfo', 'Contact Information', 40, 0, $ErrorArray);

				// See if there are any errors
				$ErrorArrayFlag = SetErrorArrayFlag($ErrorArray);

				if ($ErrorArrayFlag == 1)
				{
					// Set the InfoArray vars to current for passing back to the form
					$InfoArray['Name'] = $Name;
					$InfoArray['ContactTypeID'] = $ContactTypeID;
					$InfoArray['ContactInfo'] = $ContactInfo;

					// Construct the page
					$output = FamOrgPopWin(12);
					//Do the query and replace the ['xxx'] fields in the whole doc
					$output = Common_PopWin_Contact($output, $CurrentContactID, $CurrentFamOrgID, $CurrentMemberID, $InfoArray, $ErrorArray);
				}
				else
				{
					// determine if this is an update or insert
					if ($CurrentContactID == 'New')
					{
						$query =	"INSERT INTO Contact
									SET FamOrgID = '$CurrentFamOrgID',
									MemberID = '$MemberID',
									ContactTypeID = '$ContactTypeID',
									ContactInfo = '$ContactInfo'";
					}
					else
					{
						// stuff for update
						$query =	"UPDATE Contact
									SET MemberID = '$MemberID',
									ContactTypeID = '$ContactTypeID',
									ContactInfo = '$ContactInfo'

									WHERE ContactID = '$CurrentContactID'";
					}
					// actually do the query
					$result = mysql_query($query);
					// error check
					if(!$result)
					{echo '<br />error - could not complete UPDATE<br />';}
					else
					{
						$output = FileInclude($T_PopWin_Result);
						$output = str_replace( "['PageTitle']", "Contact Add/Update Result", $output);

					}
				}
				// clear the variables
				$_SESSION['C'] = '';


			break;
			case 23 : // Return from PopUp Volunteer Info page (case 15)
				// Process form inputs
				foreach($_POST as $key => $val)
				{$$key = $val;
//				echo "<br />".$key." => ".$val;
				}

				// Clean the variables
				list($U, $ErrorArray) = CleanTwoChoice($U, 'U', 'U', '1', '0', 'New', 'Update', $ErrorArray);
				list($VolTypeID, $ErrorArray) = CleanType($VolTypeSelect, 'VolType', 'Volunteer Type', 'VolunteerTypeArray', $ErrorArray);
				list($GroupVolunteerNotes, $ErrorArray) = CleanTextString($GroupVolunteerNotes, 'GroupVolunteerNotes', 'Group Volunteer Notes', 1000, 1, $ErrorArray);
				list($Name, $ErrorArray) = CleanTextString($GN, 'Name', 'Group Name', 120, 0, $ErrorArray);

				// See if there are any errors
				$ErrorArrayFlag = SetErrorArrayFlag($ErrorArray);

				if ($ErrorArrayFlag == 1)
				{
					// Set the InfoArray vars to current for passing back to the form
					$InfoArray['U'] = $U;
					$InfoArray['GroupVolunteerNotes'] = $GroupVolunteerNotes;
					$InfoArray['Name'] = $Name;

					// Construct the page
					$output = FamOrgPopWin(13);
					//Do the query and replace the ['xxx'] fields in the whole doc
					$output = FamOrg_PopWin_Volunteer($output, $CurrentFamOrgID, $VolTypeID, $InfoArray, $ErrorArray);
				}
				else
				{
					$queryUpdate =	"UPDATE GroupVolunteer
									SET GroupVolunteerNotes = '".$GroupVolunteerNotes."',
									VolTypeID = '".$VolTypeID."'
									WHERE (FamOrgID = '".$CurrentFamOrgID."'
									AND VolTypeID = '".$CurrentVolTypeID."')";

					$queryNew =	"INSERT INTO GroupVolunteer
										SET FamOrgID = '".$CurrentFamOrgID."',
										VolTypeID = '".$VolTypeID."',
										GroupVolunteerNotes = '".$GroupVolunteerNotes."'";				
					if ($U == 0)
					{
						$result = mysql_query($queryUpdate);
						// error check
						if(!$result)
						{
							echo '<br /> error - could not complete UPDATE VOLUNTEER<br />';
							break;
						}
					}
					if ($U == 1)
					{
						$result = mysql_query($queryNew);
						// error check
						if(!$result)
						{
							echo '<br />error - could not complete NEW VOLUNTEER<br />';
							break;
						}
					}
					$output = FileInclude($T_PopWin_Result);
					$output = str_replace( "['PageTitle']", "Volunteer Add/Update Result", $output);
					// clear the variables
					$_SESSION['V'] = '';
				}
			break;


	}


// RecordNavBar stuff - needs further encapsulation (maybe an IF statement and a flag?) but this is a start...
//

	// Bring in the NavBar
	if ($TotalRecords > 10)
	{
		// Do RecordNavBar calcs and such
		$NavBar = RecordNavBar($NavBar, $NavBar_LeftDiv, $NavBar_CenterDiv, $NavBar_RightDiv, $CurrentS, $CurrentL, $TotalRecords, "FamOrg.php");
	}
	else
	{
		$NavBar = '';
	}

	// Now do the replacements to the Main page
	$output = str_replace("['NavBar']", $NavBar, $output);
	$output = str_replace("['P']", $CurrentP, $output);
	$output = str_replace("['L']", $CurrentL, $output);
	$output = str_replace("['S']", $CurrentS, $output);


echo $output;
?>