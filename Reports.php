<?php
	session_start();

// print("Session:<pre>");
// print_r($_SESSION);
// print("</pre><br>");


	require('Includes/I_DBConnect.php');             // db connection stuff
	require('Includes/I_SetTableArrays.php');	 	 // Checks if various queries have been done previously. If so, they are stored in a $_SESSION var. If not, the query is done and the result stored in a $_SESSION var.
	require('Includes/I_Functions_PDF.php');	 	 // PDF functions. See file for more explanation/description.
	require('Includes/I_Functions_Reports.php');	 // Report specific functions.
	require('Includes/I_Functions_MembershipDirectory.php');	 // New Membership Directory
	require('Includes/I_Functions_Stats.php');		 // Statistics specific functions.
	require('Includes/I_SetErrorArrayFlag.php');	 // Checks the ErrorArray. If there are error codes, returns 1. Else returns 0.
	require('Includes/I_ReplaceErrorCodes.php');	 // Replaces error codes in the output with the appropriate error messages.
	require('Includes/I_DropDownFromArray.php');	 // Drop Down Select function from an array - might be able to replace I_Select.
	require('Includes/I_FamilyDropDownSelect.php');	 // FAMILY drop down select function - only used w/ the contact info pop up - might be able to move this to the I_Query_Common_PopWin_Contact.php file	
	require('Includes/I_FileInclude.php');           // file include function
	require('Includes/I_NameCombine.php');           // function to combines member name into a single string in a nice way - used when P=2	
	require('Includes/I_BuildAddressLabel.php');     // Builds an address label (name, address, city, etc.) from FamOrg fields.	
	require('Includes/I_MailingAddressName.php');    // Determines name for mailing address - i.e. "The Smith Family" or "John Smith" or whatever.	
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


	// Set the various templates here
	$NavBar = 'Templates/Common/T_Common_NavBar.php';
	$NavBar_LeftDiv = 'Templates/Common/T_Common_NavBar_LeftDiv.php';
	$NavBar_CenterDiv = '';
	$NavBar_RightDiv = 'Templates/Common/T_Common_NavBar_RightDiv.php';

	$T_Renewal_Body = 'Templates/Reports/T_Renewal_Body.php';
	$T_Other_Body = 'Templates/Reports/T_Other_Body.php';
	$T_Stats_Body = 'Templates/Reports/T_Stats_Body.php';

	$T_Stats_Table_CSVLink = 'Templates/Reports/T_Stats_Table_CSVLink.php';

	$T_Stats_Table_3Col_Head = 'Templates/Reports/T_Stats_Table_3Col_Head.php';
	$T_Stats_Table_3Col_Repeat = 'Templates/Reports/T_Stats_Table_3Col_Repeat.php';
	$T_Stats_Table_3Col_Foot = 'Templates/Reports/T_Stats_Table_3Col_Foot.php';

	$T_Stats_Table_6Col_Head = 'Templates/Reports/T_Stats_Table_6Col_Head.php';
	$T_Stats_Table_6Col_Repeat = 'Templates/Reports/T_Stats_Table_6Col_Repeat.php';
	$T_Stats_Table_6Col_Foot = 'Templates/Reports/T_Stats_Table_6Col_Foot.php';

	$T_Stats_Table_7Col_Head = 'Templates/Reports/T_Stats_Table_7Col_Head.php';
	$T_Stats_Table_7Col_Repeat = 'Templates/Reports/T_Stats_Table_7Col_Repeat.php';
	$T_Stats_Table_7Col_Foot = 'Templates/Reports/T_Stats_Table_7Col_Foot.php';
	
	$T_PopWin = 'Templates/T_Common_PopWin.php';	
	$T_PopWin_Renewal_LetterLabel = 'Templates/Reports/T_Renewal_PopWin_LetterLabel.php';
	$T_PopWin_Renewal_NewMemberLabel = 'Templates/Reports/T_Renewal_PopWin_NewMemberLabel.php';
	
	// not really a template but needed in a few places.
	$I_Globals = 'Includes/I_Globals.php';

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


	//Now check the ErrorArray and if there are errors, do an error routine
	$ErrorFlag = SetErrorArrayFlag($ErrorArray);
	if ($ErrorFlag == '') {$ErrorArray = '';}
	else
	{exit("<b>There was an error. Try again later. Sorry.</b>");}


	// Calculations
	//
	// records in the db are zero based so need to convert to that. (Z for Zero)
	$CurrentS_Z = CalcVar_Z($CurrentS);


	// Set ButtonArrays
	$ButtonArray[1] = array( '1', 'Reports.php?P=1', 'Renewals');
	$ButtonArray[2] = array( '2', 'Reports.php?P=2', 'Statistics');
	$ButtonArray[3] = array( '3', 'Reports.php?P=3', 'Other');



// do the appropriate SQL query and replace the appropriate fields in the Body section
// $P 1 to 3 are "main" pages using the base HTML template
// $P 11 to xxx are the actual reports

	switch($CurrentP)
	{
		case 1 : // Renewals Page
			// this is functionalized b/c other cases refer to this.
			$output = ReportsCase1($CurrentP, $ButtonArray, $T_Renewal_Body);
			break;

		case 2 : // Stats Page
			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArray);
			$output = str_replace( "['SectionTitleLeft']", 'Reports', $output);
			$output = str_replace( "['SectionTitleRight']", '', $output);
			$output = str_replace( "['SectionSubjectLeft']", '', $output);
			$output = str_replace( "['SectionSubjectRight']", '', $output);

			$Body = FileInclude($T_Stats_Body);
			
			// do query and build array to get member info: sex, DOB, zip, state, member type
			$StatsDataArray = StatsDataQuery();

			// function for stats
			list($StatsInfoArray1, $StatsInfoArray2, $StatsInfoArray3) = StatsCalc($StatsDataArray);

			// bring in templates for the 3-col tables
			$TableHead = FileInclude($T_Stats_Table_3Col_Head);
			$TableRepeat = FileInclude($T_Stats_Table_3Col_Repeat);
			$TableFoot = FileInclude($T_Stats_Table_3Col_Foot);

			// build gender table
			$GenderTable = BuildGenderTable($StatsInfoArray1, $TableHead, $TableRepeat, $TableFoot);

			// build state table
			$StateTable = BuildStateTable($StatsInfoArray1, $TableHead, $TableRepeat, $TableFoot);			
			
			// build membership type table
			$MemberTable = BuildMemberTable($StatsInfoArray1, $StatsInfoArray2, $TableHead, $TableRepeat, $TableFoot);			
			

			// bring in templates for the 6-col tables
			$TableHead = FileInclude($T_Stats_Table_6Col_Head);
			$TableRepeat = FileInclude($T_Stats_Table_6Col_Repeat);
			$TableFoot = FileInclude($T_Stats_Table_6Col_Foot);

			// build age group & gender table
			$AgeGenderTable = BuildAgeGenderTable($StatsInfoArray1, $TableHead, $TableRepeat, $TableFoot);

			// bring in templates for the 7-col tables
			$TableHead = FileInclude($T_Stats_Table_7Col_Head);
			$TableRepeat = FileInclude($T_Stats_Table_7Col_Repeat);
			$TableFoot = FileInclude($T_Stats_Table_7Col_Foot);

			// build age group & state table
			$AgeStateTable = BuildAgeStateTable($StatsInfoArray1, $TableHead, $TableRepeat, $TableFoot);
			
			// build CSV file & link
			$StatsCSVLink = FileInclude($T_Stats_Table_CSVLink);

			// $StatsCSV = BuildStatsCSV($StatsDataArray);
			
			$BodyTables = $GenderTable.$StateTable.$MemberTable.$AgeGenderTable.$AgeStateTable.$StatsCSVLink;

			// build the page body
			$Body = str_replace( "['StatsBodyTables']", $BodyTables, $Body);
			$output = str_replace( "['Body']", $Body, $output);
			break;

		case 3 : // Other Reports Page
			// Set the base template
			$output = Common_HNav_Page($CurrentP, $ButtonArray);
			$output = str_replace( "['SectionTitleLeft']", 'Reports', $output);
			$output = str_replace( "['SectionTitleRight']", '', $output);
			$output = str_replace( "['SectionSubjectLeft']", '', $output);
			$output = str_replace( "['SectionSubjectRight']", '', $output);

			$Body = FileInclude($T_Other_Body);

			$NextMonth = date('n') + 1;
			$Year = date('Y');
			if ($NextMonth == 1) {$Year += 1;}
			
			$MonthArray = Set_Check('MonthArray', '1');
			$MonthSelect = DropDownFromArray($MonthArray, 'Month', 'Number', 'Long', $NextMonth, 1);

			$output = str_replace( "['Body']", $Body, $output);
			$output = str_replace( "['Month']", $MonthSelect, $output);
			$output = str_replace( "['Year']", $Year, $output);

			break;
		case 11 : // pop-up renewal labels

			$output = RenewalPopWin('Label', $T_PopWin, $T_PopWin_Renewal_LetterLabel, "");
			break;
		
		case 12 : // pop-up renewal letters

			$output = RenewalPopWin('Letter', $T_PopWin, $T_PopWin_Renewal_LetterLabel, "");
			break;
		
		case 13 : // pop-up new member labels
		
			// set templates - base & body
			$output = FileInclude($T_PopWin);
			$Body = FileInclude($T_PopWin_Renewal_NewMemberLabel);
			// set return P (23)
			$ReturnP = 23;
			
			// Assign day/month/year values
			$MonthArray = Set_Check('MonthArray', '1');
			$ThisDay = date('d');
			$ThisYear = date('Y');
			$LastMonthYear = $ThisYear;
			$ThisMonth = date('n');
			$LastMonth = $ThisMonth - 1;
			// correct month & year if we roll back a year
			if ($LastMonth < 1)
			{
				$LastMonth = 12;
				$LastMonthYear -= 1;
			}
			// build the selects
			$BeginMonthSelect = DropDownFromArray($MonthArray, 'BeginMonth', 'Number', 'Long', $LastMonth, 1);
			$EndMonthSelect = DropDownFromArray($MonthArray, 'EndMonth', 'Number', 'Long', $ThisMonth, 1);

			// do the replacements
			$output = str_replace( "['Body']", $Body, $output);
			// replace other vars
			$output = str_replace( "['PageTitle']", "New Member Labels", $output);
			$output = str_replace( "['P']", $ReturnP, $output);
			$output = str_replace( "['FormAction']", "Reports.php", $output);
			$output = str_replace( "['Day']", $ThisDay, $output);
			$output = str_replace( "['BeginMonth']", $BeginMonthSelect, $output);
			$output = str_replace( "['EndMonth']", $EndMonthSelect, $output);
			$output = str_replace( "['BeginYear']", $ThisYear, $output);
			$output = str_replace( "['EndYear']", $LastMonthYear, $output);
			
			break;
			
		case 21 : // Renewal labels/letters - case 21
		
				// get the post data
				foreach($_POST as $key => $val)
				{$$key = $val;}

				// Clean & Validate vars
				list($Month, $ErrorArray) = CleanMonth($Month, 'Month', 'Month', 0, $ErrorArray);
				list($Year, $ErrorArray) = CleanYear($Year, 'Year', 'Year', 0, $ErrorArray);
				list($LetterLabel, $ErrorArray) = CleanTwoChoice($LetterLabel, 'LetterLabel', 'Letter/Label', '1', '2', 'Letters', 'Labels', $ErrorArray);

				// See if there are any errors
				$ErrorArrayFlag = SetErrorArrayFlag($ErrorArray);
				if ($ErrorArrayFlag == 1) { // return to Reports?P=1 with red fields
				}

				// Logic for Renewal letters/labels.
				$Logic =	"((DATE_FORMAT(FamOrg.ExpDate, '%Y') = '$Year') OR
							(DATE_FORMAT(FamOrg.ExpDate, '%Y') = '0000')) AND
							(DATE_FORMAT(FamOrg.ExpDate, '%m') = '$Month') AND
							(FamOrg.FamOrgArchive != '1') AND
							(FamOrg.BadAddress !='1')";

				// Build the group array
				$GroupArray = BuildGroupArray($Logic);
				// Add in the members to the Group Array.
				$GroupArray = BuildMemberArray($GroupArray, $Logic);


				// check for results and output a page if none
				if(!$GroupArray)
				{
					if($LetterLabel == 1) {$LL = "Letter";}
					else {$LL = "Label";}
					
					$output = RenewalPopWin($LL, $T_PopWin, $T_PopWin_Renewal_LetterLabel, "Sorry, no records match that date.");
					echo $output;
					exit;			
				}
				
				// Build the Contact & Volunteer arrays only for letters
				if($LetterLabel == 1) // Letters
				{
					// add the Contact information into the result array
					$GroupArray = BuildContactArray($GroupArray, $Logic);

					// add the Volunteer information into the result array
					$GroupArray = BuildVolunteerArray($GroupArray, $Logic);

					// begin creating the PDF
					$pdf = PDF_Create("", "RenewalLetters_".$Year."_".$Month, "", "");
					$Filename = "RenewalLetters_".$Year."_".$Month.".pdf";

					// create each label/letter from the array
					foreach($GroupArray as $row)
					{
						// build the mailing label address
						list($Adr, $line) = BuildMailingLabelAddress($row);
						$pdf = PDF_RenewalLetter($pdf, $Adr, $row);
					}

					// open the PDF in a new window
					$output = PDF_CloseOut($pdf, $Filename, "attachment");
				}
				else // Labels
				{
					// Set the filename
					$FileName = "RenewalLabels_".$Year."_".$Month;

					$output = PDFAddressLabels($FileName, $GroupArray);
				}

			break;

		case 23 : // New Member Labels
		
				// get the post data
				foreach($_POST as $key => $val)
				{$$key = $val;}

				// Clean & Validate vars
				list ($BeginDay, $BeginMonth, $BeginYear, $ErrorArray) = CleanDate($BeginDay, $BeginMonth, $BeginYear, 'BeginDay', 'BeginMonth', 'BeginYear', 'BeginDate', 'Begin Day', 'Begin Month', 'Begin Year', 'Begin Date', 0, $ErrorArray);
				list ($EndDay, $EndMonth, $EndYear, $ErrorArray) = CleanDate($EndDay, $EndMonth, $EndYear, 'EndDay', 'EndMonth', 'EndYear', 'EndDate', 'End Day', 'End Month', 'End Year', 'End Date', 0, $ErrorArray);

				// See if there are any errors
				$ErrorArrayFlag = SetErrorArrayFlag($ErrorArray);
				if ($ErrorArrayFlag == 1) { // return to Reports?P=1 with red fields
				}

				//Need to create date formats for MySQL
				$BeginDate = $BeginYear."-".$BeginMonth."-".$BeginDay;
				$EndDate = $EndYear."-".$EndMonth."-".$EndDay;

				// Logic for New Member Labels.
				$Logic =	"(FamOrg.JoinDate >= '$BeginDate') AND
							(FamOrg.JoinDate <= '$EndDate') AND
							(FamOrg.FamOrgArchive != '1') AND
							(FamOrg.BadAddress != '1')";

				// Build the group array
				$GroupArray = BuildGroupArray($Logic);
				// Add in the members to the Group Array.
				$GroupArray = BuildMemberArray($GroupArray, $Logic);

				// check for results and output a page if none
				if(!$GroupArray)
				{					
					$output = RenewalPopWin("Label", $T_PopWin, $T_PopWin_Renewal_NewMemberLabel, "Sorry, no records match that date.");
					echo $output;
					exit;			
				}

				// Set the filename
				$FileName = "NewMemberLabels_".$BeginDate."_".$EndDate;

				$output = PDFAddressLabels($FileName, $GroupArray);
			break;

			case 25 : // Single renewal form
			
				// build group array from FamOrgID
				$Logic =	"FamOrg.FamOrgID = '$CurrentFamOrgID'";

				// Build the group array
				$GroupArray = BuildGroupArray($Logic);
				// Add in the members to the Group Array.
				$GroupArray = BuildMemberArray($GroupArray, $Logic);
				// add the Contact information into the result array
				$GroupArray = BuildContactArray($GroupArray, $Logic);
				// add the Volunteer information into the result array
				$GroupArray = BuildVolunteerArray($GroupArray, $Logic);

				// begin creating the PDF
				$pdf = PDF_Create("", "RenewalForm_".$CurrentFamOrgID."_".$GroupArray[$CurrentFamOrgID]['Name'], "", "");
				$Filename = "RenewalForm_".$CurrentFamOrgID."_".$GroupArray[$CurrentFamOrgID]['Name'].".pdf";

				// create the renewal form proper
				list($Adr, $line) = BuildMailingLabelAddress($GroupArray[$CurrentFamOrgID]);
				$pdf = PDF_RenewalLetter($pdf, $Adr, $GroupArray[$CurrentFamOrgID]);

				// open the PDF in a new window
				$output = PDF_CloseOut($pdf, $Filename, "attachment");
				
			break;


			case 30 : // Newsletter address file
				// Logic used in the queries.
				$Logic =	"(FamOrg.FamOrgArchive != '1') AND
				             (FamOrg.BadAddress !='1')";

				// Build the group array
				$GroupArray = BuildGroupArray($Logic);
				// Add in the members to the Group Array.
				$GroupArray = BuildMemberArray($GroupArray, $Logic);


				// Add in the Header row
				$output = "NAME, STREET1, STREET2, STREET3, CITY, STATE, REGION, ZIP, COUNTRY\n";
				$SQ = '"'; // a single double-quote
				$FS = '","'; // field seperation
				$LB = "\n"; // line (row) break

				// build the mailing label address
				foreach($GroupArray as $row)
				{
					$output .= $SQ.MailingAddressName($row);
					$output .= $FS.$row['Street1'];
					$output .= $FS.$row['Street2'];
					$output .= $FS.$row['Street3'];
					$output .= $FS.$row['City'];
					$output .= $FS.$row['StateTwoLetter'];
					$output .= $FS.$row['Region'];
					$output .= $FS.$row['Zip']; // might need to format zip to 5+4?
					if($row['Country'] !='USA') {$output .= $FS.$row['Country'].$SQ;}
					else {$output .= $FS.$SQ;}
					$output .= $LB;
				}

				// compress the string into a gzip format
				$output = gzencode($output, 9);

				// output stuff
				$Filename = "DCRRC_NewsletterAddresses_".date('Y')."_".date('m').".csv.gz";
				// do the header stuff
				$x = File_CloseOut($output, "attachment", $Filename, "text/txt"); // is there a better way of running the function?
			break;
			
			case 31 : // Raw Stats File
				$StatsDataArray = StatsDataQuery();
				$output = BuildStatsCSV($StatsDataArray);
				
				// output stuff
				
				require($I_Globals);
				
				$Filename = $Globals['ClubNameShort']."_Raw_Stats_Data_".date('Y')."_".date('m')."_".date('d').".csv.gz";
				// do the header stuff
				$x = File_CloseOut($output, "attachment", $Filename, "text/txt"); // is there a better way of running the function?
			break;

			case 32 : // Directory (PDF)
				// Logic used in the queries.
				$Logic =	"(FamOrg.Directory != '1') AND
				             (FamOrg.FamOrgArchive != '1')";

				// Build the group array
				$GroupArray = BuildGroupArray($Logic);
				// Add in the members to the Group Array.
				$GroupArray = BuildMemberArray($GroupArray, $Logic);
				// Add the Contact information into the result array
				$GroupArray = BuildContactArray($GroupArray, $Logic);

				// do some error checking for if there is no data in GroupArray

				// Set the filename
				$FileName = "MemberDirectory_".date('Y')."_".date('m')."_".date('d')."_".(date('H')+1).date('i'); // adjust the time zone by one hour.

				$output = PDFMemberDirectory($FileName, $GroupArray);
			break;


			case 33 : // Directory (CSV)
				// Logic used in the queries.
				$Logic =	"(FamOrg.Directory != '1') AND (FamOrg.FamOrgArchive != '1')";

				// Build the group array
				$GroupArray = BuildGroupArray($Logic);
				// Add in the members to the Group Array.
				$GroupArray = BuildMemberArray($GroupArray, $Logic);
				// Add the Contact information into the result array
				$GroupArray = BuildContactArray($GroupArray, $Logic);

				// do some error checking for if there is no data in GroupArray

				// Build the array for the Directory output
						//		$LetterArray structure:
						//			$LetterArray[$FirstLetter]['Name']
						//											  ['FamOrgID']
						//											  ['NameLine']
						//											  ['CityStateZipLine']
						//											  ['EmailLine']
						//											  ['PhoneLine']
				$LetterArray = ProcessDirectoryData($GroupArray);

				// Add in the Header row
				$output = "GROUPID, NAME, CITYSTATEZIP, EMAIL, HOMEPHONE\n";
				$SQ = '"'; // a single double-quote
				$FS = '","'; // field seperation
				$LB = "\n"; // line (row) break

				// build the mailing label address
				foreach($LetterArray as $Letter)
				{
					foreach($Letter as $row)
					{
						$output .= $SQ.$row['FamOrgID'];
						$output .= $FS.$row['NameLine'];
						$output .= $FS.$row['CityStateZipLine'];
						$output .= $FS.$row['EmailLine'];
						$output .= $FS.$row['PhoneLine'].$SQ;
						$output .= $LB;
					}
				}

				// compress the string into a gzip format
				$output = gzencode($output, 9);

				// output stuff
				$Filename = "MemberDirectory_".date('Y')."_".date('m')."_".date('d')."_".(date('H')+1).date('i').".csv.gz";
				// do the header stuff
				$x = File_CloseOut($output, "attachment", $Filename, "text/txt"); // is there a better way of running the function?
			break;

			case 34 : // Club Member List for races (CSV)
				// Logic used in the queries.
				$Logic =	"(FamOrg.FamOrgArchive != '1')";

				// Build the group array
				$MemberListArray = BuildMemberListArray($Logic);

				// do some error checking for if there is no data in GroupArray


				// Add in the Header row
				$output = "LastName, FirstName, MiddleName, Suffix, DOB, Gender, City, State, JoinDate, ExpDate\n";
				$SQ = '"'; // a single double-quote
				$FS = '","'; // field seperation
				$LB = "\n"; // line (row) break

				// build the mailing label address
				foreach($MemberListArray as $row)
				{
					$output .= $SQ.$row['LName'];
					$output .= $FS.$row['FName'];
					$output .= $FS.$row['MName'];
					$output .= $FS.$row['Suffix'];
					$output .= $FS.$row['DOB'];
					$output .= $FS.$row['Gender'];
					$output .= $FS.$row['City'];
					$output .= $FS.$row['StateTwoLetter'];
					$output .= $FS.$row['JoinDate'];
					$output .= $FS.$row['ExpDate'].$SQ;
					$output .= $LB;
				}

				// compress the string into a gzip format
				$output = gzencode($output, 9);

				// output stuff
				$Filename = "MemberList_".date('Y')."_".date('m')."_".date('d').".csv.gz";
				// do the header stuff
				$x = File_CloseOut($output, "attachment", $Filename, "text/txt"); // is there a better way of running the function?
			break;

			case 36 : // Volunteer List (CSV) **** case 35 will be the PDF version.
				// Logic used in the queries.
				$Logic =	"(FamOrg.FamOrgArchive != '1')";

				// Build the group array
				$GroupArray = BuildGroupArray($Logic);
				// Add in the members to the Group Array.
				$GroupArray = BuildMemberArray($GroupArray, $Logic);
				// Add the Contact information into the result array
				$GroupArray = BuildContactArray($GroupArray, $Logic);
				// Add the Volunteer information into the result array
				$GroupArray = BuildVolunteerArray($GroupArray, $Logic);

				// do some error checking for if there is no data in GroupArray


				// Build the group array
				$VolunteerListArray = BuildVolunteerListArray($GroupArray);

 // print("VolunteerListArray:<pre>");
 // print_r($VolunteerListArray);
 // print("</pre><br>");


				// Add in the Header row
				$output = "VolunteerType, MemberID, MemberType, MemberName, Street1, Street2, Street3, City, State, Zip, HomePhone, Email1, Email2, Email3\n";
				$SQ = '"'; // a single double-quote
				$FS = '","'; // field seperation
				$LB = "\n"; // line (row) break

				// build the mailing label address
				foreach($VolunteerListArray as $Key => $VolGroup)
				{
					foreach($VolGroup as $row)
					{
					
						//need to correct PNames in Name - might be able to be more elegant here by utilizing array_walk()
						$row['Name'] = str_replace("\"", "\"\"", $row['Name']);
						$row['Street1'] = str_replace("\"", "\"\"", $row['Street1']);
						$row['Street2'] = str_replace("\"", "\"\"", $row['Street2']);
						$row['Street3'] = str_replace("\"", "\"\"", $row['Street3']);
					
						$GroupRow .= $SQ.$Key;
						$GroupRow .= $FS.$row['FamOrgID'];
						$GroupRow .= $FS.$row['MemberType'];
						$GroupRow .= $FS.$row['Name'];
						$GroupRow .= $FS.$row['Street1'];
						$GroupRow .= $FS.$row['Street2'];
						$GroupRow .= $FS.$row['Street3'];
						$GroupRow .= $FS.$row['City'];
						$GroupRow .= $FS.$row['StateTwoLetter'];
						$GroupRow .= $FS.$row['Zip'];

						if($row['Contacts'])
						{
							foreach($row['Contacts'] as $Contact)
							{
								if($Contact['ContactType'] == 'Home Phone')
								{$HomePhone = $Contact['ContactInfo'];}
								elseif(!$Email1 && $Contact['ContactType'] == 'Email')
								{$Email1 = $Contact['ContactInfo'];}				
								elseif(!$Email2 && $Contact['ContactType'] == 'Email')
								{$Email2 = $Contact['ContactInfo'];}				
								elseif(!$Email3 && $Contact['ContactType'] == 'Email')
								{$Email3 = $Contact['ContactInfo'];}				
							}
						}
						$GroupRow .= $FS.$HomePhone;
						$GroupRow .= $FS.$Email1;
						$GroupRow .= $FS.$Email2;
						$GroupRow .= $FS.$Email3.$SQ;
						$GroupRow .= $LB;
					
						// Add the GroupRow to $output
						$output .= $GroupRow;

						// clean up variables for next round
						$GroupRow = '';
						$HomePhone = '';
						$Email1 = '';
						$Email2 = '';
						$Email3 = '';
					}
				}

				// compress the string into a gzip format
				$output = gzencode($output, 9);

				// output stuff
				$Filename = "VolunteerList_".date('Y')."_".date('m')."_".date('d').".csv.gz";
				// do the header stuff
				$x = File_CloseOut($output, "attachment", $Filename, "text/txt"); // is there a better way of running the function?
			break;

			case 38 : // Expired Member List (CSV) **** case 37 will be the PDF version.
				// Get the current date so later we can find expired members
				$Current_Month = date('m');
				$Current_Year = date('Y');

				// Calculate 2 months into the past
				$TwoMonthYear = $Current_Year;
				$TwoMonth = ($Current_Month - 2);
				if ($TwoMonth < 1)
				{
					$TwoMonth += 12;
					$TwoMonthYear--;
				}
				// Be sure TwoMonth is in 2 digit format.
				if ($TwoMonth < 10)
				{$TwoMonth = "0".$TwoMonth;}

				// Logic used in the queries.
				$Logic =	"(FamOrg.FamOrgArchive != '1') AND (LEFT(FamOrg.ExpDate,7) <= ('$TwoMonthYear-$TwoMonth')) AND (LEFT(FamOrg.ExpDate,4)!=('0000'))";

				// Build the group array
				$GroupArray = BuildGroupArray($Logic);
				// Add in the members to the Group Array.
				$GroupArray = BuildMemberArray($GroupArray, $Logic);
				// Add the Contact information into the result array
				$GroupArray = BuildContactArray($GroupArray, $Logic);

				// do some error checking for if there is no data in GroupArray


				// Build the list array
				$ExpiredListArray = BuildExpiredListArray($GroupArray);

//  print("ExpiredListArray:<pre>");
//  print_r($ExpiredListArray);
//  print("</pre><br>");


				// Add in the Header row
				$output = "GroupName, GroupID, MembershipType, ExpirationDate, BadAddress, Street1, Street2, Street3, City, State, Zip, HomePhone, WorkPhone, CellPhone, Email1, Email2, Email3\n";
				$SQ = '"'; // a single double-quote
				$FS = '","'; // field seperation
				$LB = "\n"; // line (row) break

				// build the mailing label address
				foreach($ExpiredListArray as $row)
					{
					
						//need to correct PNames in Name - might be able to be more elegant here by utilizing array_walk()
						$row['Name'] = str_replace("\"", "\"\"", $row['Name']);
						$row['Street1'] = str_replace("\"", "\"\"", $row['Street1']);
						$row['Street2'] = str_replace("\"", "\"\"", $row['Street2']);
						$row['Street3'] = str_replace("\"", "\"\"", $row['Street3']);
						
						// Convert BadAddress from 0 & 1 to nothing and Yes
						if ($row['BadAddress'] == 1)
							{$row['BadAddress'] = 'Yes';}
						else {$row['BadAddress'] = '';}
					
						$GroupRow .= $SQ.$row['Name'];
						$GroupRow .= $FS.$row['FamOrgID'];
						$GroupRow .= $FS.$row['MemberType'];
						$GroupRow .= $FS.$row['ExpDate'];
						$GroupRow .= $FS.$row['BadAddress'];
						$GroupRow .= $FS.$row['Street1'];
						$GroupRow .= $FS.$row['Street2'];
						$GroupRow .= $FS.$row['Street3'];
						$GroupRow .= $FS.$row['City'];
						$GroupRow .= $FS.$row['StateTwoLetter'];
						$GroupRow .= $FS.$row['Zip'];

						if($row['Contacts'])
						{
							foreach($row['Contacts'] as $Contact)
							{
								switch($Contact['ContactType'])
								{
									case 'Home Phone':
										if(!$HomePhone){$HomePhone = $Contact['ContactInfo'];}
									break;
									case 'Work Phone':
										if(!$WorkPhone){$WorkPhone = $Contact['ContactInfo'];}
									break;
									case 'Cell Phone':
										if(!$CellPhone){$CellPhone = $Contact['ContactInfo'];}
									break;
									case 'Email':
										if(!$Email1){$Email1 = $Contact['ContactInfo'];}				
										elseif(!$Email2){$Email2 = $Contact['ContactInfo'];}				
										elseif(!$Email3){$Email3 = $Contact['ContactInfo'];}				
									break;
								}
							}
						}
						$GroupRow .= $FS.$HomePhone;
						$GroupRow .= $FS.$WorkPhone;
						$GroupRow .= $FS.$CellPhone;
						$GroupRow .= $FS.$Email1;
						$GroupRow .= $FS.$Email2;
						$GroupRow .= $FS.$Email3.$SQ;
						$GroupRow .= $LB;
					
						// Add the GroupRow to $output
						$output .= $GroupRow;

						// clean up variables for next round
						$GroupRow = '';
						$HomePhone = '';
						$WorkPhone = '';
						$CellPhone = '';
						$Email1 = '';
						$Email2 = '';
						$Email3 = '';
					
				}

//  print("Output:<pre>");
//  print_r($output);
//  print("</pre><br>");


				// compress the string into a gzip format
				$output = gzencode($output, 9);

				// output stuff
				$Filename = "ExpiredMemberList_".date('Y')."_".date('m')."_".date('d').".csv.gz";
				// do the header stuff
				$x = File_CloseOut($output, "attachment", $Filename, "text/txt"); // is there a better way of running the function?

			break;




	}

 echo $output;
?>