<?php
	session_start();

// print("Session:<pre>");
// print_r($_SESSION);
// print("</pre><br>");

	require('Includes/I_DBConnect.php');					// db connection stuff
	require('Includes/I_SetTableArrays.php');	 			// Checks if various queries have been done previously. If so, they are stored in a $_SESSION var. If not, the query is done and the result stored in a $_SESSION var.
	require('Includes/I_SetErrorCodes.php');			 	// Checks if various error code arrays have been set up. If so, they are returned. If not, they are and stored in a $_SESSION var.
	require('Includes/I_SetErrorArrayFlag.php');		    // Checks the ErrorArray. If there are error codes, returns 1. Else returns 0.
	require('Includes/I_ReplaceErrorCodes.php');			// Replaces error codes in the output with the appropriate error messages.
	require('Includes/I_DropDownFromArray.php');	 		// Drop Down Select function from an array - might be able to replace I_Select.
	require('Includes/I_FileInclude.php');					// file include function	
	require('Includes/I_NameCombine.php');					// function to combines member name into a single string in a nice way - used in M=1	
	require('Includes/I_Query_FamOrg_NameOnly.php');		// function to replace ['Name'] with the current FamOrg name
	require('Includes/I_Query_Member_MemberNameOnly.php');	// function to replace ['MemberName'] with the current Member name
	require('Includes/I_ConvertDate_1.php');				// function to convert date format from (yyyy mm dd) TO (dd mmm yyyy) input must be a date in yyyy-mm-dd format.
	require('Includes/I_Convert_Zip.php');			 		// Converts Zip from raw format to 5+4 format.
	require('Includes/I_BuildAddressLabel.php');			// Converts a row of FamOrg values to an address-label format.
	require('Includes/I_WrapText.php');						// function to wrap text
	require('Includes/I_LookUp.php');						// function to find one field value from a table.
	require('Includes/I_Common_HNav_Page.php');	 	 		// function to set horizontal menu in base page.
	require('Includes/I_Convert_DOB_to_Age.php');	 		// Converts raw DOB (in the MySQL yyyy-mm-dd format) to age in years.
	require('Includes/I_FamilyDropDownSelect.php');			// FAMILY drop down select function - only used w/ the contact info pop up - might be able to move this to the I_Query_Common_PopWin_Contact.php file	
	require('Includes/I_CalcVar_Z.php');	 				// Caluculates a zero-based value for a var. (i.e. if X is 1, X_Z = 0).
	require('Includes/I_RecordNavBar.php');	 				// Stuff for the RecordNavBar. Requires CalcVar_Z to work.
	require('Includes/I_CalcEndOfNextMonthUnix.php');		// Self explanatory.
	require('Includes/I_CalcWarningFlag.php');	 	 		// Uses ExpDate to calc. a flag. Returns Red, Yellow or None. Requires CalcEndOfNextMonth to work.
	require('Includes/I_Validate_Vars.php');				// Validates Various Vars. See script for values needed.
	require('Includes/I_Validations.php');					// Mother of all validation includes. Functions for validating/cleaning ALL data.
	require('Includes/I_CalcSectionSubject.php');	 		// Sets section subject to "Name (ARCHIVED)" if the FamOrgArchive flag = 1.

	require('Includes/I_Search_FamOrg.php');	 			// Does a search on FamOrg using a single search term and a single field.

	require('Includes/I_Query_Member_New.php');
	require('Includes/I_Query_FamOrg_Info.php');
	require('Includes/I_Query_Member_Info.php');
	require('Includes/I_Query_Member_ContactInfo.php');

	require('Includes/I_Query_Common_PopWin_Contact.php');
	require('Includes/I_Query_Common_DeleteContactInfo.php'); // function for deleting a single row from the contact table by ContactID


	// Set the various templates here
	$T_Info_Body = 'Templates/Member/T_Member_Info.php';
	$T_Group_Body = 'Templates/Member/T_Member_FamOrg_Info.php';
	$T_Group_BodyFoot = 'Templates/Member/T_Member_GroupInfoFoot.php';

	$T_New_BodyHead = 'Templates/Member/T_Member_New_BodyHead.php';
	$T_New_BodyMain = 'Templates/Member/T_Member_PopWin_Info.php';
	$T_New_BodyFoot = 'Templates/Member/T_Member_New_BodyFoot.php';

	$T_PopWin_Result = 'Templates/T_Common_PopWin_Result.php';

	$T_PopWin_ChangeGroup = 'Templates/Member/T_Member_PopWin_ChangeGroup.php';
	$T_PopWin_ChangeGroup_Repeat = 'Templates/Member/T_Member_PopWin_ChangeGroup_Repeat.php';
	$T_PopWin_ChangeGroup_Error = 'Templates/Member/T_Member_PopWin_ChangeGroup_Error.php';
	$T_PopWin_ChangeGroup_Default = 'Templates/Member/T_Member_PopWin_ChangeGroup_Default.php';


	// Validate the Current P (Page)
	$P_D = 1;
	$P_S = '';
	$P_P = $_POST['P'];
	$P_G = $_GET['P'];
	$CurrentP = Validate_Vars($P_G, $P_S, $P_P, $P_D);
	if ($CurrentP !='New'){list($CurrentP, $ErrorArray) = CleanNumber($CurrentP, 'CurrentP', '', 2, 0, $ErrorArray);}
	
	$M_D = '';
	$M_S = $_SESSION['M'];
	$M_P = $_POST['M'];
	$M_G = $_GET['M'];
	$CurrentM = Validate_Vars($M_G, $M_S, $M_P, $M_D);
	if (($CurrentM) && ($CurrentM !='New')){list($CurrentM, $ErrorArray) = CleanNumber($CurrentM, 'CurrentM', '', 5, 1, $ErrorArray);}
	$_SESSION['M'] = $CurrentM;

	// Set the CurrentFamOrgID to be consistent with the CurrentMemberID
	
	// If adding a new member, F is passed as a post
	$F_P = $_POST['F'];
	if ($F_P)
	{
		list($F_P, $ErrorArray) = CleanNumber($F_P, 'F_P', '', 4, 0, $ErrorArray);
		$CurrentF = $F_P;
	}
	else
	{$CurrentF = LookUp('FamOrgID', 'Member', 'MemberID', $CurrentM);}
	$_SESSION['SessionF'] = $CurrentF;
	

	$C_D = '';
	$C_S = $_SESSION['C'];
	$C_P = $_POST['C'];
	$C_G = $_GET['C'];
	$CurrentContactID = Validate_Vars($C_G, $C_S, $C_P, $C_D);
	if(($CurrentContactID) && ($CurrentContactID !='New')){list($CurrentContactID, $ErrorArray) = CleanNumber($CurrentContactID, 'CurrentContactID', '', 5, 1, $ErrorArray);}
	$_SESSION['C'] = $CurrentContactID;

	//Now check the ErrorArray and if there are errors, do an error routine
	$ErrorFlag = SetErrorArrayFlag($ErrorArray);
	if ($ErrorFlag == '') {$ErrorArray = '';}
	else
	{exit("<b>There was an error. Try again later. Sorry.</b>");}


	// Set ButtonArrays
	$ButtonArray[1] = array( '1', 'Member.php?P=1', 'Info');
	$ButtonArray[2] = array( '2', 'Member.php?P=2', 'Group');
	$ButtonArray[3] = array( '3', 'Member.php?P=3', 'Contact Info');

	//create and set values for ButtonArrayNew (used for new FamOrg)
	$ButtonArrayNew = $ButtonArray;	
	for ($i = 1; $i < (count($ButtonArrayNew)+1); $i++)
	{
	$ButtonArrayNew[$i][1] = '#';
	}
	$ButtonArrayNew[1][0] = 'new';

// begin working the HTML template

function MemberPopWin($CurrentP)
{
	// Bring in the base template
	$output = FileInclude('Templates/T_Common_PopWin.php');
	// Set this as a FamOrg page (not Member)
	$output = str_replace("['FormAction']", 'Member.php', $output);	
	
	switch($CurrentP)
	{
	case 10 : // Info
			//Get the body template, set the page title and success P number
			$BodyView = FileInclude('Templates/Member/T_Member_PopWin_Info.php');
			$PageTitle = "Member Edit";
			$PNum = 20;
	break;

	case 12 : // Contact
			//Get the body template, set the page title and success P number
			$BodyView = FileInclude('Templates/T_Common_PopWin_Contact.php');
			$PageTitle = "Contact Edit";
			$PNum = 22;
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
	switch($CurrentP)
	{
		case 'New' : // New member
		
			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArrayNew);
			$output = str_replace( "['SectionTitleLeft']", 'Member:', $output);
			$output = str_replace( "['SectionTitleRight']", 'Group:', $output);
			$output = str_replace( "['SectionSubjectRight']", "['Name']", $output);
			//Build body
			$Body = FileInclude($T_New_BodyHead);
			$Body .= FileInclude($T_New_BodyMain);
			$Body .= FileInclude($T_New_BodyFoot);
			//Replace ['Body'] in the output with the template
			$output = str_replace( "['Body']", $Body, $output);
			//Do the query and replace the ['xxx'] fields in the whole doc
			$output = Member_New($output, 15, $CurrentF, '', '');
			break;
		case 1 : // Member
			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArray);
			$output = str_replace( "['SectionTitleLeft']", 'Member:', $output);
			$output = str_replace( "['SectionTitleRight']", 'Group:', $output);
			$output = str_replace( "['SectionSubjectRight']", "['Name']", $output);
			//Set $BodyView to the appropriate template
			$Body = FileInclude($T_Info_Body);
			//Replace ['Body'] in the output with the template
			$output = str_replace( "['Body']", $Body, $output);
			//Do the query and replace the ['xxx'] fields in the whole doc
			$output = Member_Info($output, $CurrentM, '', '');
			// Replace ['Name'] with the group name
			$output = FamOrg_NameOnly($output, $CurrentF);
			break;
		case 2 : // Group
			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArray);
			$output = str_replace( "['SectionTitleLeft']", 'Member:', $output);
			$output = str_replace( "['SectionTitleRight']", 'Group:', $output);
			$output = str_replace( "['SectionSubjectRight']", "['Name']", $output);
			//Set $BodyView to the appropriate template
			$Body = FileInclude($T_Group_Body);
			$Body .= FileInclude($T_Group_BodyFoot);
			//Replace ['Body'] in the output with the template
			$output = str_replace( "['Body']", $Body, $output);
			//Do the query and replace the ['xxx'] fields in the whole doc
			$output = Member_MemberNameOnly($output, $CurrentM);
			$output = FamOrg_Info($output, $CurrentF, $InfoArray, $ErrorArray);
			break;
		case 3 : // Contact List
			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArray);
			$output = str_replace( "['SectionTitleLeft']", 'Member:', $output);
			$output = str_replace( "['SectionTitleRight']", 'Group:', $output);
			$output = str_replace( "['SectionSubjectRight']", "['Name']", $output);
			//Run the contact info list function...
			$output = Member_ContactInfo($output, $CurrentM, $CurrentF);
			//Replace ['MemberName'] with the member name
			$output = Member_MemberNameOnly($output, $CurrentM);
			// Replace ['Name'] with the group name
			$output = FamOrg_NameOnly($output, $CurrentF);
			break;

		case 10 : // PopUp Add/Edit Info page
			// Construct the page
			$output = MemberPopWin($CurrentP);
			//Do the query and replace the ['xxx'] fields in the whole doc
			$output = Member_Info($output, $CurrentM, '', '');
			break;

		case 12 : // PopUp Add/Edit Contact page
			// Construct the page
			$output = MemberPopWin($CurrentP);
			//Do the query and replace the ['xxx'] fields in the whole doc
			$output = Common_PopWin_Contact($output, $CurrentContactID, $CurrentF, $CurrentM, '', '');
			// clear the variables
			$_SESSION['C'] = '';
			break;
		case 13 : // Delete Contact page
			// This case # must be the same as in FamOrg.
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
			$output = str_replace( "['SectionTitleLeft']", 'Member:', $output);
			$output = str_replace( "['SectionTitleRight']", '', $output);
			//Run the contact info list function...
			$output = Member_ContactInfo($output, $CurrentM, $CurrentF);
			//Replace ['Name'] with the CurrentFamOrg name
			$output = Member_MemberNameOnly($output, $CurrentM);
			// Clear uneeded variables
			$_SESSION['C'] = '';
			break;

		case 15 : // Process New Member (return from 'New')
			// Process form inputs
			foreach($_POST as $key => $val)
			{$$key = $val;}

			// Clean the variables
			list($Title, $ErrorArray) = CleanTextString($Title, 'Title', 'Title', 25, 1, $ErrorArray);
			list($Salutation, $ErrorArray) = CleanTextString($Salutation, 'Salutation', 'Salutation', 25, 1, $ErrorArray);
			list($PName, $ErrorArray) = CleanTextString($PName, 'PName', 'Preferred Name', 25, 1, $ErrorArray);
			list($FName, $ErrorArray) = CleanTextString($FName, 'FName', 'First Name', 25, 1, $ErrorArray);
			list($MName, $ErrorArray) = CleanTextString($MName, 'MName', 'Middle Name', 25, 1, $ErrorArray);
			list($LName, $ErrorArray) = CleanTextString($LName, 'LName', 'Last Name', 25, 1, $ErrorArray);
			list($Suffix, $ErrorArray) = CleanTextString($Suffix, 'Suffix', 'Suffix', 25, 1, $ErrorArray);
			list($DOBDay, $DOBMonth, $DOBYear, $ErrorArray) = CleanDate($DOBDay, $DOBMonth, $DOBYear, 'DOBDay', 'DOBMonth', 'DOBYear', 'DOBDate', 'DOB Day', 'DOB Month', 'DOB Year', 'DOB Date', 1, $ErrorArray);
			list($Gender, $ErrorArray) = CleanTwoChoice($Gender, 'Gender', 'Gender', 'M', 'F', 'Male', 'Female', $ErrorArray);
			list($MemberNotes, $ErrorArray) = CleanTextString($MemberNotes, 'MemberNotes', 'Member Notes', 1000, 1, $ErrorArray);

			// See if there are any errors
			$ErrorArrayFlag = SetErrorArrayFlag($ErrorArray);

			if ($ErrorArrayFlag == 1)
			{
				// Set the InfoArray vars to current for passing back to the form
				$InfoArray['Title'] = $Title;
				$InfoArray['Salutation'] = $Salutation;
				$InfoArray['PName'] = $PName;
				$InfoArray['FName'] = $FName;
				$InfoArray['MName'] = $MName;
				$InfoArray['LName'] = $LName;
				$InfoArray['Suffix'] = $Suffix;
				$InfoArray['DOBDay'] = $DOBDay;
				$InfoArray['DOBMonth'] = $DOBMonth;
				$InfoArray['DOBYear'] = $DOBYear;
				$InfoArray['Gender'] = $Gender;
				$InfoArray['MemberNotes'] = $MemberNotes;

				// Set the base template
				$output = Common_HNav_Page($CurrentP, $ButtonArrayNew);
				$output = str_replace( "['SectionTitleLeft']", 'Member:', $output);
				$output = str_replace( "['SectionTitleRight']", '', $output);
				//Build body
				$Body = FileInclude($T_New_BodyHead);
				$Body .= FileInclude($T_New_BodyMain);
				$Body .= FileInclude($T_New_BodyFoot);
				//Replace ['Body'] in the output with the template
				$output = str_replace( "['Body']", $Body, $output);
				//Do the query and replace the ['xxx'] fields in the whole doc
				$output = Member_New($output, 15, $CurrentF, $InfoArray, $ErrorArray);
				break;
			}

			// Do calcs
			$DOB = $DOBYear.'-'.$DOBMonth.'-'.$DOBDay;

			// Set and do the query
			$query =	"INSERT INTO Member
							SET FamOrgID = '$CurrentF',
							Title = '$Title',
							Salutation = '$Salutation',
							PName = '$PName',
							FName = '$FName',
							MName = '$MName',
							LName = '$LName',
							Suffix = '$Suffix',
							DOB = '$DOB',
							Gender = '$Gender',
							MemberNotes = '$MemberNotes'";

			// actually do the query
			$result = mysql_query($query);
			// error check
			if(!$result)
			{echo '<br />error - could not complete INSERT<br />';}
			else
			{
				// Determine new record's FamOrgID
				$CurrentM = mysql_insert_id();
				// Set the base template
				$output = Common_HNav_Page(1, $ButtonArray);
				$output = str_replace( "['SectionTitleLeft']", 'Member:', $output);
				$output = str_replace( "['SectionTitleRight']", '', $output);
				//Set $BodyView to the appropriate template
				$Body = FileInclude($T_Info_Body);
				//Replace ['Body'] in the output with the template
				$output = str_replace( "['Body']", $Body, $output);
				//Do the query and replace the ['xxx'] fields in the whole doc
				$output = Member_Info($output, $CurrentM, '', '');
				// Set Session M
				$_SESSION['M'] = $CurrentM;
			}
		break;
		case 20 : // Return from PopUp Add/Edit Info page
				// Process form inputs
				foreach($_POST as $key => $val)
				{$$key = $val;}

				// Clean the variables
				list($Title, $ErrorArray) = CleanTextString($Title, 'Title', 'Title', 25, 1, $ErrorArray);
				list($Salutation, $ErrorArray) = CleanTextString($Salutation, 'Salutation', 'Salutation', 25, 1, $ErrorArray);
				list($PName, $ErrorArray) = CleanTextString($PName, 'PName', 'Preferred Name', 25, 1, $ErrorArray);
				list($FName, $ErrorArray) = CleanTextString($FName, 'FName', 'First Name', 25, 1, $ErrorArray);
				list($MName, $ErrorArray) = CleanTextString($MName, 'MName', 'Middle Name', 25, 1, $ErrorArray);
				list($LName, $ErrorArray) = CleanTextString($LName, 'LName', 'Last Name', 25, 1, $ErrorArray);
				list($Suffix, $ErrorArray) = CleanTextString($Suffix, 'Suffix', 'Suffix', 25, 1, $ErrorArray);
				list($DOBDay, $DOBMonth, $DOBYear, $ErrorArray) = CleanDate($DOBDay, $DOBMonth, $DOBYear, 'DOBDay', 'DOBMonth', 'DOBYear', 'DOBDate', 'DOB Day', 'DOB Month', 'DOB Year', 'DOB Date', 1, $ErrorArray);
				list($Gender, $ErrorArray) = CleanTwoChoice($Gender, 'Gender', 'Gender', 'M', 'F', 'Male', 'Female', $ErrorArray);
				list($MemberNotes, $ErrorArray) = CleanTextString($MemberNotes, 'MemberNotes', 'Member Notes', 1000, 1, $ErrorArray);

				// See if there are any errors
				$ErrorArrayFlag = SetErrorArrayFlag($ErrorArray);

				if ($ErrorArrayFlag == 1)
				{
					// Set the InfoArray vars to current for passing back to the form
					$InfoArray['FamOrgID'] = $CurrentFamOrgID;
					$InfoArray['Title'] = $Title;
					$InfoArray['Salutation'] = $Salutation;
					$InfoArray['PName'] = $PName;
					$InfoArray['FName'] = $FName;
					$InfoArray['MName'] = $MName;
					$InfoArray['LName'] = $LName;
					$InfoArray['Suffix'] = $Suffix;
					$InfoArray['DOBDay'] = $DOBDay;
					$InfoArray['DOBMonth'] = $DOBMonth;
					$InfoArray['DOBYear'] = $DOBYear;
					$InfoArray['Gender'] = $Gender;
					$InfoArray['MemberNotes'] = $MemberNotes;

					// Construct the page
					$output = MemberPopWin(10);
					//Do the query and replace the ['xxx'] fields in the whole doc
					$output = Member_Info($output, $CurrentM, $InfoArray, $ErrorArray);
					break;
				}
				else
				{						
					// Process DOB to fit the MySQL format
					$DOB = $DOBYear.'-'.$DOBMonth.'-'.$DOBDay;

					// determine if this is an update or insert
					if ($CurrentM == 'New')
					{
						$query = ""; //	stuff for insert
					}
					else
					{
						// stuff for update
						$query =	"UPDATE Member
									SET Title = '".$Title."',
									Salutation = '".$Salutation."',
									PName = '".$PName."',
									FName = '".$FName."',
									MName = '".$MName."',
									LName = '".$LName."',
									Suffix = '".$Suffix."',
									DOB = '".$DOB."',
									Gender = '".$Gender."',
									MemberNotes ='".$MemberNotes."'
									WHERE MemberID = '".$CurrentM."'";
					}
					// actually do the query
					$result = mysql_query($query);
					// error check
					if(!$result)
					{echo '<br />error - could not complete UPDATE<br />';}
					else
					{
						$output = FileInclude($T_PopWin_Result);
						$output = str_replace( "['PageTitle']", "Member Add/Update Result", $output);
					}
				}
			break;
/*			case 21 : // Return from PopUp Volunteer Info page (case 11)
				// Process form inputs
				foreach($_POST as $key => $val)
				{$$key = $val;}

				// Clean the variables
				list($U, $ErrorArray) = CleanTwoChoice($U, 'U', 'U', '1', '0', 'New', 'Update', $ErrorArray);
				list($VolTypeID, $ErrorArray) = CleanType($VolTypeSelect, 'VolType', 'Volunteer Type', 'VolunteerTypeArray', $ErrorArray);
				list($MemberVolunteerNotes, $ErrorArray) = CleanTextString($MemberVolunteerNotes, 'MemberVolunteerNotes', 'Member Volunteer Notes', 1000, 1, $ErrorArray);
				list($MemberName, $ErrorArray) = CleanTextString($MN, 'MemberName', 'Member Name', 120, 0, $ErrorArray);

				// See if there are any errors
				$ErrorArrayFlag = SetErrorArrayFlag($ErrorArray);

				if ($ErrorArrayFlag == 1)
				{
					// Set the InfoArray vars to current for passing back to the form
					$InfoArray['U'] = $U;
					$InfoArray['MemberVolunteerNotes'] = $MemberVolunteerNotes;
					$InfoArray['MemberName'] = $MemberName;

					// Construct the page
					$output = MemberPopWin(11);
					//Do the query and replace the ['xxx'] fields in the whole doc
					$output = Member_PopWin_Volunteer($output, $CurrentM, $VolTypeID, $InfoArray, $ErrorArray);
				}
				else
				{
					$queryUpdate =	"UPDATE MemberVolunteer
									SET MemberVolunteerNotes = '".$MemberVolunteerNotes."',
									VolTypeID = '".$VolTypeID."'
									WHERE (MemberID = '".$CurrentM."'
									AND VolTypeID = '".$CurrentVolTypeID."')";

					$queryNew =	"INSERT INTO MemberVolunteer
										SET MemberID = '".$CurrentM."',
										VolTypeID = '".$VolTypeID."',
										MemberVolunteerNotes = '".$MemberVolunteerNotes."'";				
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
*/			case 22 : // Return from PopUp Contact Info page
			// Process form inputs

				foreach($_POST as $key => $val)
				{$$key = $val;}

				// Clean & Validate vars
				list($Name, $ErrorArray) = CleanTextString($GN, 'Name', 'Group Name', 30, 0, $ErrorArray);
				list($ContactTypeID, $ErrorArray) = CleanType($ContactTypeDropDown, 'ContactType', 'Contact Type', 'ContactTypeArray', $ErrorArray);
				list($MemberID, $ErrorArray) = CleanIDNumber($MemberID, 'MemberID', 'Member Name', $ErrorArray);
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
					$output = MemberPopWin(12);
					//Do the query and replace the ['xxx'] fields in the whole doc
					$output = Common_PopWin_Contact($output, $CurrentContactID, $CurrentF, $MemberID, $InfoArray, $ErrorArray);
				}
				else
				{
					// determine if this is an update or insert
					if ($CurrentContactID == 'New')
					{
						$query =	"INSERT INTO Contact
									SET FamOrgID = '$CurrentF',
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

		case 30 : // ChangeGroup Pop Up - Result & Default

				// set output string (at head of this doc)
				$output = FileInclude($T_PopWin_ChangeGroup);

				foreach($_POST as $key => $val)
				{$$key = $val;}

				// Clean the variables
				list($SS, $ErrorArray) = CleanTextString($SS, 'SS', 'Search String', 30, 0, $ErrorArray);

				// See if there are any errors
				$ErrorArrayFlag = SetErrorArrayFlag($ErrorArray);

				if($SS && ($ErrorArrayFlag != 1)) // indicates a search has been done
				{
					$Repeat = FileInclude($T_PopWin_ChangeGroup_Repeat);
					// function to find *ALL* groups (FamOrgID & Group Name) & put them into an array
					$FamOrgActiveArray = Set_Check('FamOrgActiveArray', '');

					if ((ereg('^([0-9])+', $SS)) && (ereg('[^a-zA-Z_\-]', $SS))) // Search Group ID
					{
						// Set query of Array to search for FamOrgID
						$CG_Array = FamOrgSearch('FamOrgID', $SS, 0);
					}
					else // Search Group Name
					{
						// Set query of Array to search for Name
						$CG_Array = FamOrgSearch('Name', $SS, 1);
					}

					// Set the repeat section
					if ($CG_Array == '')
					{
						$Repeat = FileInclude($T_PopWin_ChangeGroup_Error);
					}
					else
					{
						$RepeatTemplate = FileInclude($T_PopWin_ChangeGroup_Repeat);
						$Repeat = '';

						foreach($CG_Array as $row)
						{
							$Address = stripslashes(BuildAddressLabel($row));

							$Repeat .= $RepeatTemplate;

							$Repeat = str_replace( "['FamOrgID']", $row['FamOrgID'], $Repeat);
							$Repeat = str_replace( "['Name']", stripslashes($row['Name']), $Repeat);
							$Repeat = str_replace( "['Address']", $Address, $Repeat);

						}
					}
				}
				else // no search has been done so must be default.
				{
					$Repeat = FileInclude($T_PopWin_ChangeGroup_Default);
				}

				// now replace the ['xxx']s
				$output = str_replace( "['PageTitle']", 'Change Group', $output); // this will be modified when the current Member info is put into an array. (future)
				$output = str_replace( "['NorID_N']", $NorID_N, $output);
				$output = str_replace( "['NorID_ID']", $NorID_ID, $output);
				$output = str_replace( "['SS']", $SS, $output);			
				$output = str_replace( "['Repeat']", $Repeat, $output);


		break;
		case 31 : // Return from ChangeGroup Pop Up
				
				// Process form inputs
				foreach($_POST as $key => $val)
				{$$key = $val;}

				// Clean the variables
				list($FamOrgID, $ErrorArray) = CleanType($ID, 'FamOrg', 'Group ID', 'FamOrgActiveArray', $ErrorArray);

				// See if there are any errors
				$ErrorArrayFlag = SetErrorArrayFlag($ErrorArray);

				if ($ErrorArrayFlag == 1)
				{
					// Construct the page
						echo '<br /><b>ERROR</b> - That was an invalid Group ID. Sorry.<br />';
						break;
				}
				else
				{
					$query =	"UPDATE Member
								SET FamOrgID = '".$FamOrgID."'
								WHERE MemberID = '".$CurrentM."'";

					$result = mysql_query($query);
					// error check
					if(!$result)
					{
						echo '<br /><b>ERROR</b> - could not change the Group. Sorry.<br />';
						break;
					}
					$output = FileInclude($T_PopWin_Result);
					$output = str_replace( "['PageTitle']", "Change Group Result", $output);
				}
			break;


	}
echo $output;

?>