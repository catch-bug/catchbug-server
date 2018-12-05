<?php
/**
 * @Project: RollBugServer
 * @User   : mira
 * @Date   : 5.12.18
 * @Time   : 13:31
 */


/**
 * @param \mysqli $mysqli
 * @param string  $filePath
 *
 * @return bool|string
 */
function restoreDatabaseTables(\mysqli $mysqli, string $filePath)
{
  // Temporary variable, used to store current query
  $templine = '';

  // Read in entire file
  $lines = file($filePath);

  $error = '';

  // Loop through each line
  foreach ($lines as $line){
    // Skip it if it's a comment
    if($line === '' || strpos($line, '--') === 0){
      continue;
    }

    // Add this line to the current segment
    $templine .= $line;

    // If it has a semicolon at the end, it's the end of the query
    if (substr(trim($line), -1, 1) === ';'){
      // Perform the query
      if(!$mysqli->query($templine)){
        $error .= 'Error performing query "<b>' . $templine . '</b>": ' . $mysqli->error . '<br /><br />';
      }

      // Reset temp variable to empty
      $templine = '';
    }
  }
  return !empty($error)?$error:true;
}
