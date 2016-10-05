<?php
	session_start();

// print("Session:<pre>");
// print_r($_SESSION);
// print("</pre><br>");

	require('Includes/I_DBConnect.php');             // db connection stuff
	require('Includes/I_SetTableArrays.php');	 	 // Checks if various queries have been done previously. If so, they are stored in a $_SESSION var. If not, the query is done and the result stored in a $_SESSION var.
	require('Includes/I_FileInclude.php');           // file include function	
	require('Includes/I_Common_HNav_Page.php');	 	 // function to set horizontal menu in base page.
	require('Includes/I_CountRecords.php');	 		 // Counts rows in a table.
	require('Includes/I_CalcVar_Z.php');	 		 // Caluculates a zero-based value for a var. (i.e. if X is 1, X_Z = 0).
	require('Includes/I_WrapText.php');	 		 	 // Wraps text usage: WrapText($str, $l)  $str = string to process. $l = length of line in characters.
	require('Includes/I_SortIndicator.php');	 	 // Evaluates where to put the indicator (an image) for which field the result set is sorted on.
	require('Includes/I_RecordNavBar.php');	 		 // Stuff for the RecordNavBar. Requires CalcVar_Z to work.

	require('Includes/I_Validate_Vars.php');		 // Validates L (# of records/page). Local value of L is "$CurrentL"
													 // Validates S (Starting record). Local value of S is "$CurrentS". Default is 1
	require('Includes/I_Validations.php');			 // Mother of all validation includes. Functions for validating/cleaning ALL data.
	require('Includes/I_SetErrorArrayFlag.php');	 // Checks the ErrorArray. If there are error codes, returns 1. Else returns 0.
	require('Includes/I_ReplaceErrorCodes.php');	 // Replaces error codes in the output with the appropriate error messages.


//Set the currency format to USD - this will go into a global variable-type of file in the future.
setlocale(LC_MONETARY, 'en_US');


	// Set the various templates here
	$Admin_BodyDefault = 'Templates/Admin/T_Admin_BodyDefault.php';
	$Admin_PopUpBase = 'Templates/T_Common_PopWin.php';

	$NavBar = 'Templates/Common/T_Common_NavBar.php';
	$NavBar_LeftDiv = 'Templates/Common/T_Common_NavBar_LeftDiv.php';
	$NavBar_CenterDiv = '';
	$NavBar_RightDiv = 'Templates/Common/T_Common_NavBar_RightDiv.php';
	
	$Admin_BodyHead_Contact = 'Templates/Admin/T_Admin_BodyHead_Contact.php';
	$Admin_BodyRepeat_Contact = 'Templates/Admin/T_Admin_BodyRepeat_Contact.php';
	$Admin_BodyFoot_Contact = 'Templates/Admin/T_Admin_BodyFoot_Contact.php';

	$Admin_BodyHead_Member = 'Templates/Admin/T_Admin_BodyHead_Member.php';
	$Admin_BodyRepeat_Member = 'Templates/Admin/T_Admin_BodyRepeat_Member.php';
	$Admin_BodyFoot_Member = 'Templates/Admin/T_Admin_BodyFoot_Member.php';

	$Admin_BodyHead_Volunteer = 'Templates/Admin/T_Admin_BodyHead_Volunteer.php';
	$Admin_BodyRepeat_Volunteer = 'Templates/Admin/T_Admin_BodyRepeat_Volunteer.php';
	$Admin_BodyFoot_Volunteer = 'Templates/Admin/T_Admin_BodyFoot_Volunteer.php';

	$Admin_PopWin_Contact = 'Templates/Admin/T_Admin_PopWin_Contact.php';
	$Admin_PopWin_Member = 'Templates/Admin/T_Admin_PopWin_Member.php';
	$Admin_PopWin_Volunteer = 'Templates/Admin/T_Admin_PopWin_Volunteer.php';


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
	$L_S = $_SESSION['L'];
	$L_P = $_POST['L'];
	if ($P_G == '' || $P_G != $P_S) // basically, if we're coming from somewhere else, set L to default.
	{$CurrentL = $L_D;}
	else {$CurrentL = Validate_Vars($L_G, $L_S, $L_P, $L_D);}
	list($CurrentL, $ErrorArray) = CleanNumber($CurrentL, 'CurrentL', '', 4, 0, $ErrorArray);
	$_SESSION['L'] = $CurrentL;

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

// print("<br>Session:<pre>");
// print_r($_SESSION);
// print("</pre><br>");


	// Calculations
	//
	// records in the db are zero based so need to convert to that. (Z for Zero)
	$CurrentS_Z = CalcVar_Z($CurrentS);

	// Set ButtonArrays
	$ButtonArray[1] = array( '1', 'Admin.php?P=1', 'Administration');
	$ButtonArray[2] = array( '2', 'Admin.php?P=2', 'Contact Types');
	$ButtonArray[3] = array( '3', 'Admin.php?P=3', 'Member Types');
	$ButtonArray[4] = array( '4', 'Admin.php?P=4', 'Volunteer Types');

// do the appropriate SQL query and replace the appropriate fields in the Body section
//
//

	switch($CurrentP)
	{
		case 1 : // Base (instruction) page

			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArray);
			//Build Body
			$Body = FileInclude($Admin_BodyDefault);
				
			// Do the replacements
			$output = str_replace("['Body']", $Body, $output);
			$output = str_replace("['NavBar']", '', $output);

		break;

		case 2 : // Contact Type Admin page

			// Set the Sort vars
			$SortArray[1] = 'ContactType.ContactTypeID';
			$SortArray[2] = 'ContactType.ContactType';

			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArray);

			//Build Body
			$Body = FileInclude($Admin_BodyHead_Contact);

			// Need to count the number of rows
			$TotalRecords = CountRecords('ContactType');
			
			// Do query and add in query results
			$query =	"SELECT ContactType.ContactTypeID, ContactType.ContactType
							 FROM ContactType
							 ORDER BY ".$SortArray[$CurrentSort]."
							 LIMIT ".$CurrentS_Z.",".$CurrentL;

			$result = mysql_query($query);
			// error check
			if(!$result)
			{echo '<br />error - could not complete the query. My deepest apologies.<br />';}

			// Set the Body Repeat file
			$BodyRepeatSection = fileInclude($Admin_BodyRepeat_Contact);

			// Assign variables to row data (variable name = field name
			while($row = mysql_fetch_array($result))
			{
				foreach($row as $key => $val)
				{$$key = stripslashes($val);} 

				// Make new row
				$BodyRepeat = $BodyRepeatSection;

				// Replace placeholders in the new row
				$BodyRepeat = str_replace("['CTID']", $ContactTypeID, $BodyRepeat);
				$BodyRepeat = str_replace("['ContactType']", $ContactType, $BodyRepeat);
				
				// Add new row to body
				$Body .= $BodyRepeat;
			}
				
			// We've got the head and body. Now add footer to the Body section
			$Body .= fileInclude($Admin_BodyFoot_Contact);
				
		break;
		case 3 : // Member Type Admin page

			// Set the Sort vars
			$SortArray[1] = 'MemberType.MemberTypeID';
			$SortArray[2] = 'MemberType.MemberType';
			$SortArray[3] = 'MemberType.DuesRate';
			$SortArray[4] = 'MemberType.DuesFrequency';

			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArray);

			//Build Body
			$Body = fileInclude($Admin_BodyHead_Member);

			// Need to count the number of rows
			$TotalRecords = CountRecords('MemberType');

			// Do query and add in query results
			$query =	"SELECT MemberType.MemberTypeID, MemberType.MemberType, MemberType.DuesRate, MemberType.DuesFrequency, MemberType.MemberTypeNotes
							 FROM MemberType
							 ORDER BY ".$SortArray[$CurrentSort]."
							 LIMIT ".$CurrentS_Z.",".$CurrentL;

			$result = mysql_query($query);
			// error check
			if(!$result)
			{echo '<br />error - could not complete the query. My deepest apologies.<br />';}

			// Set the Body Repeat file
			$BodyRepeatSection = fileInclude($Admin_BodyRepeat_Member);

				// Assign variables to row data (variable name = field name
				while($row = mysql_fetch_array($result))
				{
					foreach($row as $key => $val)
					{$$key = stripslashes($val);} 

					// Do needed calcs
					$DuesRate = money_format('%.2n', $DuesRate); // formats as currency
					$MemberTypeNotes = WrapText($MemberTypeNotes, 50);

					// Make new row
					$BodyRepeat = $BodyRepeatSection;

					// Replace placeholders in the new row
					$BodyRepeat = str_replace("['MTID']", $MemberTypeID, $BodyRepeat);
					$BodyRepeat = str_replace("['MemberType']", $MemberType, $BodyRepeat);
					$BodyRepeat = str_replace("['DuesRate']", $DuesRate, $BodyRepeat);
					$BodyRepeat = str_replace("['DuesFrequency']", $DuesFrequency, $BodyRepeat);
					$BodyRepeat = str_replace("['MemberTypeNotes']", $MemberTypeNotes, $BodyRepeat);

					// Add new row to body
					$Body .= $BodyRepeat;
				}
				
				// We've got the head and body. Now add footer to the Body section
				$Body .= fileInclude($Admin_BodyFoot_Member);

		break;
		case 4 : // Volunteer Type Admin page

			// Set the Sort vars
			$SortArray[1] = 'VolunteerType.VolTypeID';
			$SortArray[2] = 'VolunteerType.VolType';

			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArray);

			//Build Body
			$Body = fileInclude($Admin_BodyHead_Volunteer);

			// Need to count the number of rows
			$TotalRecords = CountRecords('VolunteerType');

			// Do query and add in query results
			$query =	"SELECT VolunteerType.VolTypeID, VolunteerType.VolType, VolunteerType.VolDescription
							 FROM VolunteerType
							 ORDER BY ".$SortArray[$CurrentSort]."
							 LIMIT ".$CurrentS_Z.",".$CurrentL;

			$result = mysql_query($query);
			// error check
			if(!$result)
			{echo '<br />error - could not complete the query. My deepest apologies.<br />';}

			// Set the Body Repeat file
			$BodyRepeatSection = fileInclude($Admin_BodyRepeat_Volunteer);

				// Assign variables to row data (variable name = field name
				while($row = mysql_fetch_array($result))
				{
					foreach($row as $key => $val)
					{$$key = stripslashes($val);} 

					// Do needed cal to wrap VolDescription text
					$VolDescription = WrapText($VolDescription, 50);

					// Make new row
					$BodyRepeat = $BodyRepeatSection;

					// Replace placeholders in the new row
					$BodyRepeat = str_replace("['VTID']", $VolTypeID, $BodyRepeat);
					$BodyRepeat = str_replace("['VolType']", $VolType, $BodyRepeat);
					$BodyRepeat = str_replace("['VolDescription']", $VolDescription, $BodyRepeat);

					// Add new row to body
					$Body .= $BodyRepeat;
				}
				
				// We've got the head and body. Now add footer to the Body section
				$Body .= fileInclude($Admin_BodyFoot_Volunteer);

		break;

		
		case 12 : // Pop Up Contact

		//Set the page templates
		$output = FileInclude($Admin_PopUpBase);
		$Body = FileInclude($Admin_PopWin_Contact);

		//Check for CTID	
		if ($_GET['CTID'])
		{$CTID = $_GET['CTID'];}
		elseif ($_POST['CTID'])
		{$CTID = $_POST['CTID'];}
		else
		{$CTID = '';}

		// check for new & do logic
		if ($CTID == 'New')
		{
			$PageTitle = 'Add Contact Type';
			$ContactType = '';
		}
		else
		{
			$PageTitle = 'Edit Contact Type';

			list($CTID, $ErrorArray) = CleanType($CTID, 'ContactType', 'Contact Type ID', 'ContactTypeArray', $ErrorArray);

			if ($ErrorArray['E_ContactType'] == '')
			{
				$query = "SELECT ContactType.ContactTypeID, ContactType.ContactType
							FROM ContactType
							WHERE ContactType.ContactTypeID = ".$CTID;

				// actually do the query
				$result = mysql_query($query);
				// error check
				if(!$result)
				{echo '<br /><b>ERROR</b> - could not complete ContactType Query.<br />';}
				else
				{
					//put the result of the query into an array
					$row = mysql_fetch_array($result);
					$ContactType = stripslashes($row['ContactType']);
				}
			}
			else
			{$Body = "<br /><b>ERROR</b> - Invalid Contact Type.";}
		}

		// build page
		$output = str_replace("['Body']", $Body, $output);
		$output = str_replace("['FormAction']", "Admin.php", $output);
		$output = str_replace("['P']", 21, $output);

		$output = str_replace("['PageTitle']", $PageTitle, $output);
		$output = str_replace("['CTID']", $CTID, $output);
		$output = str_replace("['ContactType']", $ContactType, $output);

		break;		

		case 13 : // Pop Up Member

		//Set the page templates
		$output = FileInclude($Admin_PopUpBase);
		$Body = FileInclude($Admin_PopWin_Member);

		//Check for CTID	
		if ($_GET['MTID'])
		{$MTID = $_GET['MTID'];}
		elseif ($_POST['MTID'])
		{$MTID = $_POST['MTID'];}
		else
		{$MTID = '';}

		// check for new & do logic
		if ($MTID == 'New')
		{
			$PageTitle = 'Add Member Type';
			$MemberType = '';
			$DuesRate = '';
			$DuesFrequency = '1';
			$MemberTypeNotes = '';
		}
		else
		{
			$PageTitle = 'Edit Member Type';

			list($MTID, $ErrorArray) = CleanType($MTID, 'MemberType', 'Member Type ID', 'MemberTypeArray', $ErrorArray);

			if ($ErrorArray['E_MemberType'] == '')
			{
				$query = "SELECT MemberType.MemberType, MemberType.DuesRate, MemberType.DuesFrequency, MemberType.MemberTypeNotes
							FROM MemberType
							WHERE MemberType.MemberTypeID = ".$MTID;

				// actually do the query
				$result = mysql_query($query);
				// error check
				if(!$result)
				{echo '<br />error - could not complete MemberType Query.<br />';}
				else
				{
					//Assign result field values to vars of the same name
					while($row = mysql_fetch_array($result))
					{
						foreach($row as $key => $val)
						{$$key = stripslashes($val);} 
					}
				}	
			}
			else
			{$Body = "<br /><b>ERROR</b> - Invalid Member Type.";}
		}

		// build page
		$output = str_replace("['Body']", $Body, $output);
		$output = str_replace("['FormAction']", "Admin.php", $output);
		$output = str_replace("['P']", 22, $output);

		$output = str_replace("['PageTitle']", $PageTitle, $output);
		$output = str_replace("['MTID']", $MTID, $output);
		$output = str_replace("['MemberType']", $MemberType, $output);
		$output = str_replace("['DuesRate']", $DuesRate, $output);
		$output = str_replace("['DuesFrequency']", $DuesFrequency, $output);
		$output = str_replace("['MemberTypeNotes']", $MemberTypeNotes, $output);

		break;

		case 14 : // Pop Up Volunteer

		//Set the page templates
		$output = FileInclude($Admin_PopUpBase);
		$Body = FileInclude($Admin_PopWin_Volunteer);

		//Check for CTID	
		if ($_GET['VTID'])
		{$VTID = $_GET['VTID'];}
		elseif ($_POST['VTID'])
		{$VTID = $_POST['VTID'];}
		else
		{$VTID = '';}

		// check for new & do logic
		if ($VTID == 'New')
		{
			$PageTitle = 'Add Volunteer Type';
			$VolType = '';
			$VolDescription = '';
		}
		else
		{
			$PageTitle = 'Edit Volunteer Type';
			list($VTID, $ErrorArray) = CleanType($VTID, 'VolType', 'Volunteer Type ID', 'VolunteerTypeArray', $ErrorArray);
			if ($ErrorArray['E_VolType'] == '')
			{
				$query = "SELECT VolunteerType.VolType, VolunteerType.VolDescription
							FROM VolunteerType
							WHERE VolunteerType.VolTypeID = ".$VTID;

				// actually do the query
				$result = mysql_query($query);
				// error check
				if(!$result)
				{echo '<br />error - could not complete Volunteer Type Query.<br />';}
				else
				{
					//put the result of the query into an array
					$row = mysql_fetch_array($result);
					$VolType = stripslashes($row['VolType']);
					$VolDescription = stripslashes($row['VolDescription']);
				}
			}
			else
			{$Body = "<br /><b>ERROR</b> - Invalid Volunteer Type.";}
		}

		// build page
		$output = str_replace("['Body']", $Body, $output);
		$output = str_replace("['FormAction']", "Admin.php", $output);
		$output = str_replace("['P']", 23, $output);

		$output = str_replace("['PageTitle']", $PageTitle, $output);
		$output = str_replace("['VTID']", $VTID, $output);
		$output = str_replace("['VolType']", $VolType, $output);
		$output = str_replace("['VolDescription']", $VolDescription, $output);

		break;

		case 21 : // return from Contact PopWin
		
		$CTID = $_POST['CTID'];
		list($ContactType, $ErrorArray) = CleanTextString($_POST['ContactType'], 'ContactTypeText', 'Contact Type', 40, 0, $ErrorArray);

		// see if new or not
		if ($CTID == 'New')
		{
			$query =	"INSERT INTO ContactType
						SET ContactType = '".$ContactType."'";				
		}
		elseif($CTID != '')
		{
			$query =	"UPDATE ContactType
						SET ContactType = '".$ContactType."'
						WHERE ContactTypeID = '".$CTID."'";

			list($CTID, $ErrorArray) = CleanType($CTID, 'ContactType', 'Contact Type ID', 'ContactTypeArray', $ErrorArray);;
		}

		// See if there are any errors
		$ErrorArrayFlag = SetErrorArrayFlag($ErrorArray);

		if ($ErrorArrayFlag == 1)
		{
			$PageTitle = 'Add/Edit Contact Type - ERROR';

			//Set the page templates
			$output = FileInclude($Admin_PopUpBase);
			
			if($ErrorArray['ContactType'] !='')
			{$Body = "<br /><b>ERROR</b> - Invalid Contact Type.";}
			else
			{$Body = FileInclude($Admin_PopWin_Contact);}

			// build page
			$output = str_replace("['Body']", $Body, $output);
			$output = str_replace("['FormAction']", "Admin.php", $output);
			$output = str_replace("['P']", 21, $output);

			$output = str_replace("['PageTitle']", $PageTitle, $output);
			$output = str_replace("['CTID']", $CTID, $output);
			$output = str_replace("['ContactType']", stripslashes($ContactType), $output);		
		}
		else
		{
			// actually do the query
			$result = mysql_query($query);
			// error check
			if(!$result)
			{echo '<br />error - could not complete ContactType INSERT/UPDATE<br />';}
			else
			{
				$output = FileInclude('Templates/T_Common_PopWin_Result.php');
				$output = str_replace( "['PageTitle']", "Contact Type Add/Update Result", $output);
			}

			// need to force the ContactTypeArray to reload since changes have been made.
			$result = Set_Check('ContactTypeArray', 1);
		}
		break;

		case 22 : // return from Member PopWin
		
		$MTID = $_POST['MTID'];
		list($MemberType, $ErrorArray) = CleanTextString($_POST['MemberType'], 'MemberTypeText', 'Member Type', 40, 0, $ErrorArray);
		list($DuesRate, $ErrorArray) = CleanCurrency($_POST['DuesRate'], 'DuesRate', 'Dues Rate', 7, 0, $ErrorArray);
		list($DuesFrequency, $ErrorArray) = CleanNumber($_POST['DuesFrequency'], 'DuesFrequency', 'Dues Frequency', 1, 0, $ErrorArray);
		list($MemberTypeNotes, $ErrorArray) = CleanTextString($_POST['MemberTypeNotes'], 'MemberTypeNotes', 'Member Type Notes', 1000, 1, $ErrorArray);

		// see if new or not
		if ($MTID == 'New')
		{
			$query =	"INSERT INTO MemberType
						SET MemberType = '".$MemberType."',
							DuesRate = '".$DuesRate."',
							DuesFrequency = '".$DuesFrequency."',
							MemberTypeNotes = '".$MemberTypeNotes."'";
		}
		elseif($MTID != '')
		{
			$query =	"UPDATE MemberType
						SET MemberType = '".$MemberType."',
							DuesRate = '".$DuesRate."',
							DuesFrequency = '".$DuesFrequency."',
							MemberTypeNotes = '".$MemberTypeNotes."'
							WHERE MemberTypeID = '".$MTID."'";

			list($MTID, $ErrorArray) = CleanType($MTID, 'MemberType', 'Member Type ID', 'MemberTypeArray', $ErrorArray);
		}

		// See if there are any errors
		$ErrorArrayFlag = SetErrorArrayFlag($ErrorArray);

		if ($ErrorArrayFlag == 1)
		{
			$PageTitle = 'Add/Edit Member Type - ERROR';
			//Set the page templates
			$output = FileInclude($Admin_PopUpBase);

			if($ErrorArray['MemberType'] !='')
			{$Body = "<br /><b>ERROR</b> - Invalid Member Type.";}
			else
			{$Body = FileInclude($Admin_PopWin_Member);}

			// build page
			$output = str_replace("['Body']", $Body, $output);
			$output = str_replace("['FormAction']", "Admin.php", $output);
			$output = str_replace("['P']", 22, $output);

			$output = str_replace("['PageTitle']", $PageTitle, $output);
			$output = str_replace("['MTID']", $MTID, $output);
			$output = str_replace("['MemberType']", stripslashes($MemberType), $output);
			$output = str_replace("['DuesRate']", $DuesRate, $output);
			$output = str_replace("['DuesFrequency']", $DuesFrequency, $output);
			$output = str_replace("['MemberTypeNotes']", stripslashes($MemberTypeNotes), $output);
		}
		else
		{
			// actually do the query
			$result = mysql_query($query);
			// error check
			if(!$result)
			{echo '<br />error - could not complete MemberType INSERT/UPDATE<br />';}
			else
			{
				$output = FileInclude('Templates/T_Common_PopWin_Result.php');
				$output = str_replace( "['PageTitle']", "Member Type Add/Update Result", $output);
			}

			// need to force the MemberTypeArray to reload since changes have been made.
			$result = Set_Check('MemberTypeArray', 1);
		}
		break;

		case 23 : // return from Volunteer PopWin

		$VTID = $_POST['VTID'];
		list($VolTypeText, $ErrorArray) = CleanTextString($_POST['VolType'], 'VolTypeText', 'Volunteer Type', 30, 0, $ErrorArray);
		list($VolDescription, $ErrorArray) = CleanTextString($_POST['VolDescription'], 'VolDescription', 'Volunteer Description', 300, 1, $ErrorArray);

		// see if new or not
		if ($VTID == 'New')
		{
			$query =	"INSERT INTO VolunteerType
						SET VolType = '".$VolTypeText."',
							VolDescription = '".$VolDescription."'";
		}
		elseif($VTID != '')
		{
			$query =	"UPDATE VolunteerType
						SET VolType = '".$VolTypeText."',
							VolDescription = '".$VolDescription."'
						WHERE VolTypeID = '".$VTID."'";

			list($VTID, $ErrorArray) = CleanType($VTID, 'VolType', 'Volunteer Type ID', 'VolunteerTypeArray', $ErrorArray);
		}

		// See if there are any errors
		$ErrorArrayFlag = SetErrorArrayFlag($ErrorArray);

		if ($ErrorArrayFlag == 1)
		{
			$PageTitle = 'Add/Edit Volunteer Type - ERROR';
			//Set the page templates
			$output = FileInclude($Admin_PopUpBase);

			if($ErrorArray['VolType'] !='')
			{$Body = "<br /><b>ERROR</b> - Invalid Volunteer Type.";}
			else
			{$Body = FileInclude($Admin_PopWin_Volunteer);}
			
			// build page
			$output = str_replace("['Body']", $Body, $output);
			$output = str_replace("['FormAction']", "Admin.php", $output);
			$output = str_replace("['P']", 23, $output);

			$output = str_replace("['PageTitle']", $PageTitle, $output);
			$output = str_replace("['VTID']", $VTID, $output);
			$output = str_replace("['VolType']", stripslashes($VolTypeText), $output);
			$output = str_replace("['VolDescription']", stripslashes($VolDescription), $output);
		}
		else
		{
			// actually do the query
			$result = mysql_query($query);
			// error check
			if(!$result)
			{echo '<br />error - could not complete VolunteerType INSERT/UPDATE<br />';}
			else
			{
				$output = FileInclude('Templates/T_Common_PopWin_Result.php');
				$output = str_replace( "['PageTitle']", "Volunteer Type Add/Update Result", $output);
			}

			// need to force the VolunteerTypeArray to reload since changes have been made.
			$result = Set_Check('VolunteerTypeArray', 1);
		}
		break;
	}

// RecordNavBar stuff - needs further encapsulation (maybe an IF statement and a flag?) but this is a start...
//

	// Bring in the NavBar
	if ($TotalRecords > 10)
	{
		// Do RecordNavBar calcs and such
		$NavBar = RecordNavBar($NavBar, $NavBar_LeftDiv, $NavBar_CenterDiv, $NavBar_RightDiv, $CurrentS, $CurrentL, $TotalRecords, "Admin.php");
	}
	else
	{
		$NavBar = '';
	}

	// Now do the replacements to the Main page
	$output = str_replace( "['SectionTitleLeft']", 'Administration', $output);
	$output = str_replace( "['SectionTitleRight']", '', $output);
	$output = str_replace( "['SectionSubjectLeft']", '', $output);
	$output = str_replace( "['SectionSubjectRight']", '', $output);
	$output = str_replace("['Body']", $Body, $output);
	$output = str_replace("['NavBar']", $NavBar, $output);
	$output = str_replace("['P']", $CurrentP, $output);
	$output = str_replace("['L']", $CurrentL, $output);
	$output = str_replace("['S']", $CurrentS, $output);


	// Sort
	//
	$SortCode = "<img src=\"Images/SortArrow.gif\" width=\"10\" height=\"10\" border=\"0\">";
	$output = SortIndicator($CurrentSort, $SortCode, $SortArray, $output);

	// now the error codes
	$output = ReplaceErrorCodes($output, $ErrorArray);

echo $output;
?>