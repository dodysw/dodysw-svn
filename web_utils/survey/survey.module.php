<?



# ------- Database Configuration ----------- #
$db_host = 'ns2.javanic.com';
$db_login = 'UUUUUUUUU';
$db_password = 'MMMMMMMMMMM';
$db_database = 'metro';

$admin_password = 'aaaaaaaaaaaa';
$ans_char = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

mysql_connect($db_host, $db_login, $db_password) or die ('could not connect to database');
mysql_select_db ($db_database) or die ('could not select database');


function ischecked ($name) {
    if ($_REQUEST[$name] != '')
        return ' checked ';
    return '';
}

function parse_surveydata ($data) {
    $data = trim($data);
    $questions = explode("\r\n\r\n",$data);
    $questions = array_map('trim',$questions);
    $qarr = array();
    $idx = 0;
    foreach ($questions as $question) {
        $idx += 1;
        $answers = array();
        $param_1 = '';
        $param_2 = array();
        $required = 0;
        list($mode,$buffer) = explode(':',$question,2);   //detect either M (multiple choice), E (essay)
        $prop = explode(',',$mode);
        $mode = $prop[0];
        if ($mode == 'M' or $mode == 'O' or $mode == 'C') {
            $temp_arr = explode("\r\n",$buffer);
            if (count($temp_arr) <= 1)
                exit("<h3>$idx: question multiple choices need at least 1 question</h3><pre>$question</pre>");
            $text = $temp_arr[0];
            $answers = array_slice($temp_arr,1);
            $answers = array_map('trim',$answers);
        }
        elseif ($mode == 'E' or $mode == 'T') {
            $text = $buffer;
        }
        elseif ($mode == 'U') { //custom HTML. constructed on multiple question(s)
            $temp_arr = explode("\r\n",$buffer);
            if (count($temp_arr) <= 1)
                exit("<h3>$idx: custom HTML need at least 1 question</h3><pre>$question</pre>");
            $text = $temp_arr[0];
            $c_questions = array_slice($temp_arr,1);
            $c_questions = array_map('trim',$c_questions);
            foreach ($c_questions as $c_question) { //question formatted as ie:  1c:My first question
                list($c_key,$c_value) = explode(':',$c_question); //1a,R:Kalau beli baju, biasanya di mana?
                $c_key = explode(',',$c_key);
                $c_required = 0;
                if (in_array('R',array_slice($c_key,1)))
                    $c_required = 1;
                $param_2[] = array('id'=>$c_key[0],'text'=>$c_value,'required'=>$c_required);
            }
            #~ $text = $buffer;
            $param_1 = $prop[1];
        }
        else {
            die("<h3>$idx/1: invalid question mode [$mode]</h3><pre>$question</pre>");
        }
        if (in_array('R',$prop))
            $required = 1;
        $qarr[] = array('mode'=>$mode, 'text'=>$text, 'answers'=>$answers, 'required'=>$required, 'param_1'=>$param_1, 'param_2'=>$param_2);
    }
    #~ echo '<p>Hasil Parse Data<br><pre>';print_r($qarr);echo '</pre>';
    return $qarr;
}

function show_surveydata ($data) {
    #~ echo '<pre>';print_r($data);echo '</pre>';

    #~ //IN CASE admin, we want to show the result answers next to the answers
    #~ if ($_SESSION['auth'] == 1) {
        #~ global $survey_id;
        #~ $lines_a = '';
        #~ $sql = "select * from answers where survey_id='$survey_id'";
        #~ $res = mysql_query($sql);
        #~ $result_summary = array();
        #~ while ($row = mysql_fetch_array($res)) {
            #~ $answer_data = unserialize($row['data']);
            #~ $idx = 0;
            #~ foreach ($answer_data as $answer) {
                #~ if ($data[$idx]['mode'] == 'M' or $data[$idx]['mode'] == 'O' or $data[$idx]['mode'] == 'C') {
                    #~ if (is_array($answer)) {    // case for checkboxes
                        #~ foreach ($answer as $ans) {
                            #~ //for Lainnya, ans is array of answer id + essay
                            #~ if (is_array($ans)) {
                                #~ $ans = $ans[0];
                            #~ }
                            #~ $result_summary[$idx][$ans] += 1;
                        #~ }
                    #~ }
                    #~ else {
                        #~ $result_summary[$idx][$answer] += 1;
                    #~ }
                #~ }
                #~ $idx++;
            #~ }
        #~ }
    #~ }


    foreach ($data as $question_id=>$q) {
        $number = $question_id + 1;
        // show RED when method is POST yet this question has not been answered yet
        echo "\r\n<dl>";
        $req_str = $q['required']? '<b><span style=color:red>*</span></b> ' : '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST' and $q['required'] and (!array_key_exists('q^'.$question_id, $_REQUEST) or $_REQUEST['q^'.$question_id] == ''))
            #~ echo "$req_str<b><span style=color:red>$number. {$q['text']}</span></b>";
            echo "<span style=color:red>[belum terisi]</span> $req_str$number. {$q['text']}";
        else
            echo "$req_str$number. {$q['text']}";
        if ($q['mode'] == 'M') {
            foreach ($q['answers'] as $answer_id=>$answer_desc) {
                $checked = '';
                #~ if (strval($_REQUEST['q^'.$question_id]) === strval($answer_id))
                if ($_REQUEST['q^'.$question_id] != '' and $_REQUEST['q^'.$question_id] == $answer_id)
                    $checked = 'checked';
                if (substr($answer_desc,0,1) == '@') {  //jawaban lainnya
                    $answer_desc = substr($answer_desc,1);
                    echo "\r\n<dd><input type='radio' name='q^$question_id' value='$answer_id' $checked> $answer_desc <input type='text' name='q^$question_id^$answer_id^@' value='".$_REQUEST['q^'.$question_id.'^'.$answer_id.'^@']."'>";
                }
                else {
                    echo "\r\n<dd><input type='radio' name='q^$question_id' value='$answer_id' $checked> $answer_desc";
                }
                #~ if ($_SESSION['auth'] == 1) {   // let's show result for this question
                    #~ $ca = $result_summary[$question_id][$answer_id];
                    #~ $ca = $ca? $ca: 0;
                    #~ echo '<b> = '.$ca.'</b>';
                #~ }
            }
        }
        elseif ($q['mode'] == 'O') {    //SELECT
            echo "\r\n<dd><select name='q^$question_id'><option value=''></option>";
            foreach ($q['answers'] as $answer_id=>$answer_desc) {
                $checked = '';
                #~ if (strval($_REQUEST['q^'.$question_id]) === strval($answer_id))
                if ($_REQUEST['q^'.$question_id] != '' and $_REQUEST['q^'.$question_id] == $answer_id)
                    $checked = 'selected';
                echo "<option value='$answer_id' $checked> $answer_desc";
                #~ if ($_SESSION['auth'] == 1) {   // let's show result for this question
                    #~ $ca = $result_summary[$question_id][$answer_id];
                    #~ $ca = $ca? $ca: 0;
                    #~ echo '<b> = '.$ca.'</b>';
                #~ }
                echo "</option>";
            }
            echo '</select>';
        }
        elseif ($q['mode'] == 'C') {    //CHECKBOXES
            #~ $idx2 = 0;
            #~ echo '['.$question_id,']';
            #~ print_r($_REQUEST['q^'.$question_id]);
            #~ die();
            echo "\r\n<dd>";
            foreach ($q['answers'] as $answer_id=>$answer_desc) {
                $checked = '';
                if ($_REQUEST['q^'.$question_id] != '' and in_array($answer_id,$_REQUEST['q^'.$question_id]))
                    $checked = 'checked';
                if (substr($answer_desc,0,1) == '@') {  //jawaban lainnya
                    $answer_desc = substr($answer_desc,1);
                    echo "\r\n<br><input type='checkbox' name='q^".$question_id."[]' value='$answer_id' $checked> $answer_desc <input type='text' name='q^$question_id^$answer_id^@' value='".$_REQUEST['q^'.$question_id.'^'.$answer_id.'^@']."'>";
                }
                else {
                    echo "\r\n<input type='checkbox' name='q^".$question_id."[]' value='$answer_id' $checked> $answer_desc &nbsp; ";
                }
                #~ if ($_SESSION['auth'] == 1) {   // let's show result for this question
                    #~ $ca = $result_summary[$question_id][$answer_id];
                    #~ $ca = $ca? $ca: 0;
                    #~ echo '<b> = '.$ca.'</b>';
                #~ }
                #~ $idx2++;
            }
        }
        elseif ($q['mode'] == 'E') {
            echo "\r\n<dd><textarea rows=10 cols=70 name='q^$question_id'>{$_REQUEST['q^'.$question_id]}</textarea>";
        }
        elseif ($q['mode'] == 'T') {
            echo "\r\n<dd><input type=text name='q^$question_id' value='{$_REQUEST['q^'.$question_id]}'>";
        }
        elseif ($q['mode'] == 'U') {
            //first of all, extract question variable
            foreach ($q['param_2'] as $c_question) {

                if ($_SERVER['REQUEST_METHOD'] == 'POST' and $c_question['required']) {
                    //iterate request for any answers
                    $found = 0;
                    foreach ($_REQUEST as $key=>$value) { //qx^1a^*
                        if (strstr($key,'qx^'.$c_question['id'].'^') and $value != '')
                            $found = 1;
                    }
                    if ($found == 0)
                        $_REQUEST['qy^'.$c_question['id']] = '<span style=color:red>[belum terisi]</span> '.$c_question['text'];
                    else
                        $_REQUEST['qy^'.$c_question['id']] = $c_question['text'];
                }
                else {
                    $_REQUEST['qy^'.$c_question['id']] = $c_question['text'];
                }
            }
            include $q['param_1'];
        }
        else {
            exit("<h3>unsuported question mode [{$q['mode']}]</h3><pre>{$q['text']}</pre>");
        }
        echo "</dl>";
    }
    if ($_SESSION['auth'] == 1) {
        echo "<a href='{$_SERVER['PHP_SELF']}?admin=1'><b>&lt;&lt; Back to Survey Engine Administration</b></a>";
    }
}

function check_surveysubmit ($data) {
    //make sure all required questions are answered
    foreach ($data as $question_id=>$q) {
        if ($q['mode'] == 'U') {
            foreach ($q['param_2'] as $c_question) {    //check all questions
                if ($c_question['required'] == 1) {
                    //iterate request for any answers
                    $found = 0;
                    foreach ($_REQUEST as $key=>$value) { //qx^1a^*
                        if (strstr($key,'qx^'.$c_question['id'].'^'))
                            $found = 1;
                    }
                    if (!$found) {
                        return 0;
                    }
                }
            }
        }
        if ($q['required'] and (!array_key_exists('q^'.$question_id, $_REQUEST) or $_REQUEST['q^'.$question_id] == '')) {
            return 0;
        }
    }
    return 1;
}

function save_surveysubmit ($data) {
    #~ echo '<h1>XX</h1>';
    $survey_id = addslashes($_REQUEST['id']);
    $answer_data = array();
    #~ echo '<pre>';print_r($data);exit;
    foreach ($data as $question_id=>$q) {
        #~ echo '<br>qid ',$question_id, 'mode', $q['mode'];
        $answer_value = $_REQUEST['q^'.$question_id];   //answer value contain the index of answer (ie 0 for first answer)
        #~ echo 'a1'.$answer_value;
        #~ print_r($q);
        #~ exit;
        if ($q['mode'] == 'U') {   //custom HTML
            //just retrieve all qx^* and qy^* request, and let the reporting do the tricks
            $custom_requests = array();
            //get all sub-question id for this question
            $subquestions = array();
            foreach ($q['param_2'] as $key2=>$value2) {
                #~ echo '<br> - '.$value2['id'];
                $subquestions[$key2] = $value2['id'];
                $realkey = 'qx^'.$value2['id'].'^';
                $subquestions_answers = array();
                foreach ($_REQUEST as $key=>$value) {   //get all answers for this subquestions
                    $char3 = substr($key,0,strlen($realkey));
                    #~ echo '<br> ',$key,': is ',$realkey,' equal ', $char3;
                    if ($char3 == $realkey) {
                        $subquestions_answers[] = $value;
                    }
                }
                #~ echo '<br>value ',print_r($subquestions_answers);
                $custom_requests[] = $subquestions_answers;
            }
            #~ echo '<br>Appending ',print_r($custom_requests);
            $answer_data[] = $custom_requests;
        }
        elseif (is_array($answer_value)) {  //checkboxes
            $new_answers = array();
            foreach ($answer_value as $ans) {   //rebuild array of answers
                if (substr($q['answers'][$ans],0,1) == '@') {   //for multiple choice, let's check whether the answer text is @Lainnya
                    $more_answer_value = $_REQUEST['q^'.$question_id.'^'.$ans.'^@']; //retrieve additional essay for this answer
                    $new_answers[] = array($ans, $more_answer_value);
                }
                else {
                    $new_answers[] = $ans;
                }
            }
            $answer_data[] = $new_answers;
        }
        else {
            if (count($q['answers']) > 0 and substr($q['answers'][$answer_value],0,1) == '@') {   //for multiple choice, let's check whether the answer text is @Lainnya
                $more_answer_value = $_REQUEST['q^'.$question_id.'^'.$answer_value.'^@']; //retrieve additional essay for this answer
                $answer_data[] = array($answer_value, $more_answer_value);
            }
            else {
                $answer_data[] = $answer_value;
            }
        }
    }
    #~ echo '<hr>Parse Submit Data<br><pre>';print_r($answer_data);exit;
    $serial_data = addslashes(serialize($answer_data));
    $sql = "insert into answers (survey_id,data,ipaddr,create_date,referer,marker) values ('$survey_id','$serial_data','{$_SERVER['REMOTE_ADDR']}',Now(),'{$_REQUEST['referer']}','{$_REQUEST['marker']}')";
    $res = mysql_query($sql);
}

function show_thankyou ($title,$text) {
    echo <<<__END__
<html><head><title>$title</title><link rel="stylesheet" type="text/css" href="clean.css"></head><body>
$text
</body></html>
__END__;
}

function handle_admin () {
    global $my_license,$my_css;
    $page = $_REQUEST['page'];

    if ($page == '') {
        $page = 'logon';
        if ($_SESSION['auth'] == 1)
            $page = 'list';
    }

    if ($page == 'logon') {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') { // validate user logon
            global $admin_password;
            if ($_REQUEST['password'] == $admin_password) { // set session
                $_SESSION['auth'] = 1;
                header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?admin=1&page=list');
                exit;
            }
            else die('Invalid password');
        }
        if ($_SESSION['auth'] != 1)
            echo "<form method=post><input type=password name=password><input type=hidden name=admin value=1><input type=submit></form>";
    }

    if ($page == 'logout') {
        $_SESSION['auth'] = 0;
        header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?admin=1');
        exit;
    }

    if ($page == 'toggle_active') {
        $survey_id = addslashes($_REQUEST['id']);

        $active = $_REQUEST['status']? 0: 1;
        $sql = "update survey set active='$active' where id='$survey_id'";
        mysql_query($sql);
        header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?admin=1&page=list');
        exit;
    }

    if ($page == 'purge') {
        $survey_id = addslashes($_REQUEST['id']);
        $sql = "delete from answers where survey_id='$survey_id'";
        mysql_query($sql);
        header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?admin=1&page=list');
        exit;
    }

    if ($page == 'delete') {
        $survey_id = addslashes($_REQUEST['id']);
        $sql = "delete from answers where survey_id='$survey_id'";
        mysql_query($sql);
        $sql = "delete from survey where id='$survey_id'";
        mysql_query($sql);
        header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?admin=1&page=list');
        exit;
    }

    if ($page == 'list') {
        //show list of surveys, answer count, active or not,  and link to export xls
        $sql = 'select * from survey';
        $res = mysql_query($sql);
        echo "<html><head><title>Survey Engine Administration</title>$my_css</head><body>";
        echo '<ul>';
        while ($row = mysql_fetch_array($res)) {
            //check how many answer for this survey
            $sql = "select 1 from answers where survey_id='{$row['id']}'";
            $answer_count = mysql_num_rows(mysql_query($sql));
            echo "<li>{$row['name']} (<i>{$row['title']}</i>)<br>
                = active:<b>{$row['active']}</b> | resp(s):<b>$answer_count</b> | id:<b>{$row['id']}</b> | ip check:<b>{$row['ip_check']}</b> | start:<b>{$row['valid_from']}</b> | expire:<b>{$row['valid_to']}</b> <br>
                * <a href='{$_SERVER['PHP_SELF']}?admin=1&page=export&id={$row['id']}'>excel</a>
                * <a href='{$_SERVER['PHP_SELF']}?id={$row['id']}'>show</a>
                * <a href='{$_SERVER['PHP_SELF']}?admin=1&page=toggle_active&id={$row['id']}&status={$row['active']}'>toggle active</a>
                * <a href='{$_SERVER['PHP_SELF']}?admin=1&page=purge&id={$row['id']}' onclick=\"javascript: return confirm('Are you sure?')\">purge survey (warning!)</a>
                * <a href='{$_SERVER['PHP_SELF']}?admin=1&page=delete&id={$row['id']}' onclick=\"javascript: return confirm('Are you sure?')\">delete survey (warning!)</a>
                * <a href='p/tbl_change.php?lang=id-iso-8859-1&server=1&db=metrosurvey&table=survey&pos=0&session_max_rows=30&disp_direction=horizontal&repeat_cells=100&dontlimitchars=0&primary_key=+%60id%60+%3D+%27".$row['id']."%27&goto=../{$_SERVER['PHP_SELF']}?admin=1'>edit</a>
                ";
        }
        echo '</ul>';
        echo '<p>';
        echo '<br>* <a href="p/tbl_change.php?lang=en-iso-8859-1&server=1&db=metrosurvey&table=survey&goto=tbl_properties.php">New Survey</a> (see <a href=sample.txt>sample</a> for proper format in field data)';
        echo '<br>* <a href="p/db_details_export.php?lang=en-iso-8859-1&server=1&db=metrosurvey&goto=db_details_export.php">Backup Database</a>';
        echo '<br>* <a href="'.$_SERVER['PHP_SELF'].'?admin=1&page=logout">Logout</a>';

        echo $my_license;
    }

    if ($page == 'export') {
        //show list of surveys, answer count, active or not,  and link to export xls
        $survey_id = addslashes($_REQUEST['id']);
        export_excel($survey_id);
    }
    exit;
}

function export_excel ($survey_id) {
    // return excel
    $sql = "select name,title,description,thanks,data,active from survey where id='$survey_id'";
    $res = mysql_query($sql);
    if (!mysql_num_rows($res))
        die ('survey not found');
    $row = mysql_fetch_array($res);
    $survey_name = $row['name'];
    $title = $row['title'];
    $description = $row['description'];
    $thanks = $row['thanks'];
    $data = $row['data'];

    $data = parse_surveydata($data);
    $question_count = count($data);

    //prepare question strings like: <td class=xl29 style='border-left:none'>q1</td>

    $lines_q = '';
    global $ans_char;
    #~ echo '<pre>';print_r($data);exit;
    foreach ($data as $question) {
        #~ echo '<pre>';print_r($question);exit;
        if ($question['mode'] == 'U') {
            //special for custom, show all user defined questions (qy^) here
            foreach ($question['param_2'] as $c_question) {
                $lines_q .= "<td class=xl29 style='border-left:none'>{$c_question['text']}</td>";
            }
            continue;
        }
        #~ //we want to show like Berapa 1+1? (A=10,B=30,D=40)
        #~ $answer_str = '';
        #~ if (is_array($question['answers']) and count($question['answers']) > 0) {
            #~ $tmp_arr = array();
            #~ foreach ($question['answers'] as $answer_id=>$answer_desc) {
                #~ $tmp_arr[] = $ans_char[$answer_id].'='.$answer_desc;
            #~ }
            #~ $answer_str = ' ('.join(',',$tmp_arr).')';
        #~ }
        $lines_q .= "<td class=xl29 style='border-left:none'>{$question['text']}$answer_str</td>";
    }

    //prepare answer strings
    $lines_a = '';
    global $ans_char;
    $sql = "select * from answers where survey_id='$survey_id'";
    $res = mysql_query($sql);
    $answer_count = mysql_num_rows($res);
    $result_summary = array();
    while ($row = mysql_fetch_array($res)) {
        #~ echo '<pre>';print_r($row);exit;
        $lines_a .= "<tr height=17 style='height:12.75pt'>";
        $lines_a .= "<td height=17 class=xl28 align=left style='height:12.75pt;border-top:none'>{$row['ipaddr']} [{$row['id']}]</td>";
        $lines_a .= "<td height=17 class=xl28 align=left style='height:12.75pt;border-top:none'>{$row['marker']}</td>";
        $lines_a .= "<td height=17 class=xl28 align=left style='height:12.75pt;border-top:none'>{$row['referer']}</td>";
        $answer_data = unserialize($row['data']);
        #~ echo '<pre>';print_r($answer_data);exit;
        $idx = -1;
        $idx2 = -1; //used for statisti
        #~ echo '<pre>';print_r($answer_data);exit;
        foreach ($answer_data as $answer) {
            $idx++;
            $idx2++;
            if ($data[$idx]['mode'] == 'U') {
                $c_q_coll = array();
                #~ echo '<pre>';print_r($answer);exit;
                foreach ($answer as $key=>$value) {    //[qx^1a^1] => Sogo
                    $temp_arr = explode('^',$key);
                    $c_q_coll[] = $value;
                }
                foreach ($c_q_coll as $key=>$value) {
                    foreach ($value as $ans) {
                        $result_summary[$idx2][$ans] += 1;
                    }
                    $answer = join(',',$value);
                    $sss = "<td class=xl28 align=left style='border-top:none;border-left:none'>$answer</td>";
                    #~ echo '<br>'.$sss;
                    $lines_a .= $sss;
                    $idx2++;
                }
                //$lines_a .= "<td class=xl28 align=left style='border-top:none;border-left:none'>$answer</td>";
                continue;
            }
            elseif ($data[$idx]['mode'] == 'M') {
                if (is_array($answer)) {    //lainnya
                    #~ $answer = $data[$idx]['answers'][$answer[0]];
                    $result_summary[$idx2][$data[$idx]['answers'][$answer[0]]] += 1;
                    $answer = $answer[1];
                    #~ $answer = $ans_char[$answer[0]].'='.$answer[1];
                    #~ $answer = $data[$idx]['answers'][$answer[0]];
                }
                else {
                    #~ $answer = $ans_char[$answer];
                    $answer = $data[$idx]['answers'][$answer];
                    $result_summary[$idx2][strval($answer)] += 1;
                }
            }
            elseif ($data[$idx]['mode'] == 'O') {
                #~ $answer = $ans_char[$answer];
                $answer = $data[$idx]['answers'][$answer];
                #~ echo $answer;exit;
                $result_summary[$idx2][strval($answer)] += 1;
            }
            elseif ($data[$idx]['mode'] == 'C') { //combo, answer may be multiple/array
                for ($i = 0; $i < count($answer); $i++) {
                    if (is_array($answer[$i])) {    //lainnya
                        $result_summary[$idx2][$data[$idx]['answers'][$answer[$i][0]]] += 1;
                        #~ $answer[$i] = $ans_char[$answer[$i][0]].'='.$answer[$i][1];
                        $answer[$i] = $data[$idx]['answers'][$answer[$i][0]].'='.$answer[$i][1];
                    }
                    else {
                        #~ $answer[$i] = $ans_char[$answer[$i]];
                        $answer[$i] = $data[$idx]['answers'][$answer[$i]];
                        #~ $answer[$i] = $data[$idx]['answers'][$answer];
                        $result_summary[$idx2][$answer[$i]] += 1;
                    }
                }
                $temp_answer = array();
                ksort($answer);
                foreach ($answer as $key=>$value) {
                    $temp_answer[] = $value;
                }
                $answer = join(',',$temp_answer);
            }
            else {
                $result_summary[$idx2] = array();
            }
            $lines_a .= "<td class=xl28 align=left style='border-top:none;border-left:none'>$answer</td>";

        }
        $lines_a .= "</tr>";
    }
    // show summary
    $lines_a .= "<tr height=17 style='height:12.75pt'>";
    $lines_a .= "<td height=17 class=xl28 align=left style='height:12.75pt;border-top:none' colspan=3>Summary:</td>";
    #~ $lines_a .= "<td height=17 class=xl28 align=left style='height:12.75pt;border-top:none'></td>";
    #~ $lines_a .= "<td height=17 class=xl28 align=left style='height:12.75pt;border-top:none'></td>";
    #~ echo '<pre>';print_r($result_summary);
    #~ exit;
    foreach ($result_summary as $answers) {
        // $answers is array like A=>1 B=>6...
        $answer = array();
        ksort($answers);
        foreach ($answers as $key=>$value) {
            $answer[] = "$key=$value";
        }
        $lines_a .= "<td class=xl28 align=left style='border-top:none;border-left:none'>".join(',',$answer)."</td>";
    }

    header('Content-type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="'.urlencode($survey_name).'.xls"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: no-cache');
    header("Expires: 0");
    include 'x.tpl.php';
    exit;
}
# ------- START PROGRAM -------------#
session_start();

# ------- data retrieval ----------- #
if ($_REQUEST['admin'] != '')
    handle_admin ();

if ($_REQUEST['id'] == '')
    die('no survey is provided, exiting.');

$survey_id = addslashes($_REQUEST['id']);

//take survey from database
$sql = "select name,title,description,thanks,data,active,ip_check,valid_from,valid_to from survey where id='$survey_id'";
$res = mysql_query($sql);
if (!mysql_num_rows($res))
    die ('survey not found');
$row = mysql_fetch_array($res);
if ($row['active'] == 0)
    die ('survey is not active');

$title = $row['title'];
$description = $row['description'];
$thanks = $row['thanks'];
$data = $row['data'];

#~ echo $row['ip_check'];
#~ print_r($_REQUEST);
if ($_REQUEST['done']) {
    if ($thanks == '')
        $thanks = '<h1 align=center>Thank You</h1>';
    show_thankyou($title,$thanks);
    exit;
}

//make sure valid from/to is honored
$sql = "select 1 from survey where id='$survey_id' and valid_from <= Now() and valid_to >= Now()";
if (!mysql_num_rows(mysql_query($sql))) {
    #die ('survey has not started or has expired');
    header('Location: http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/'.'sorry.html');
    exit;
}


if ($row['ip_check']) {
    //make sure that no answers coming from this ip for this survey
    $sql = "select 1 from answers where survey_id='$survey_id' and ipaddr='{$_SERVER['REMOTE_ADDR']}'";
    if (mysql_num_rows(mysql_query($sql))) {    //show thankyou message
        header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?id='.$survey_id.'&done=1');
        exit;
        //setcookie("username", "$username", time()+315360000, "/", "www.mydomain.com");
    }
}

$data = parse_surveydata($data);

# ------------ submit processor --------------- #
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (check_surveysubmit($data)) {   //validate required fields
        save_surveysubmit($data); //save answers into db
        header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?id='.$survey_id.'&done=1');
        exit;
    }
}

//check referer and marker
#~ echo 'referer:'.$_SERVER['HTTP_REFERER'];
if ($_REQUEST['referer'] == '' and $_SERVER['HTTP_REFERER'] != '') {
    $_REQUEST['referer'] = $_SERVER['HTTP_REFERER'];
}
if ($_REQUEST['marker'] == '' and $_REQUEST['m'] != '') {
    $_REQUEST['marker'] = $_REQUEST['m'];
}

include 'main.tpl.php';
#~ print_r($_REQUEST);
exit;
?>