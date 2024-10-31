<?php
/**
 * Encodes the string to base64 then to url
 * @param $str
 * @return string encoded
 */
function wpst_encrypt_param($str, $encode_level = 4)
{
    if(!empty($str) && !is_array($str) || (int)$str==0){
        $str = wpst_caesar_encrypt($str);
        for($a=0; $a<$encode_level; $a++){
            $str = base64_encode($str);
        }
        $str = str_replace("=","",$str);
        return rawurlencode($str);
    }else{
        return $str;
    }
}


/**
 * Decodes a url_encoded and base64 encoded string to its equivalent normal string
 * Reverse function of encrypt_param();
 * @param $str
 * @see encrypt_param()
 * @return string decoded
 */
function wpst_decrypt_param($str, $decode_level = 4)
{
    if(!empty($str) && !is_array($str) || (int)$str==0){
        $str = rawurldecode($str);
        for($a=0; $a<$decode_level; $a++){
            $str = base64_decode($str);
        }
        return wpst_caesar_decrypt($str);
    }else{
        return $str;
    }
}

/**
 * Encrypts a two dimensional array preserving the key and composes a string of concatenated encrypted elements
 * @param array $param
 * @return string
 */
function wpst_encrypt_array($param=array())
{
    $param_str="";
    $ctr=0;
    foreach($param as $key=>$value){
        if($ctr>0) $param_str .= "&";
        $param_str.="$key=".wpst_encrypt_param($value);
        $ctr++;
    }
    return $param_str;
}


/**
 * Reverse function of encrypt_array
 * @param array $arr_encrypted
 * @return array
 */
function wpst_decrypt_array($arr_encrypted=array())
{
    $arr_decrypted = array();
    foreach($arr_encrypted as $key=>$value){
        $arr_decrypted[$key] = wpst_decrypt_param($value);
    }
    return $arr_decrypted;
}

function wpst_caesar_encrypt($str,$move=5)
{
    $chars = wpst_get_characters();
    $total_chars=count($chars);
    if ($move > $total_chars) $move=$total_chars;
    $arr_str = str_split($str);
    $enc_str = "";
    foreach($arr_str as $i=>$c){
        if(($pos=array_search($c,$chars))!==false){
            $pos+=$move;
            if($pos>=$total_chars){
                $pos=$pos - $total_chars;
            }
            $enc_str.=$chars[$pos];
        }else{
            $enc_str.=$c;
        }
    }
    return $enc_str;
}

function wpst_caesar_decrypt($str,$move=5)
{
    $chars = wpst_get_characters();
    $total_chars=count($chars);
    if($move > $total_chars) $move=$total_chars;
    $arr_str=str_split($str);
    $enc_str = "";
    foreach ($arr_str as $i=>$c){
        if (($pos = array_search($c,$chars))!==false){
            $pos -= $move;
            if ($pos < 0) {
                $pos = $total_chars+$pos;
            }
            $enc_str .= $chars[$pos];
        } else {
            $enc_str .= $c;
        }
    }
    return $enc_str;
}

function wpst_get_characters()
{
    $chars=array("A","a","Z","z","B","b","Y","y","C","c",
        "X","x","D","d","W","w","E","e","V","v",
        "F","f","U","u","G","g","T","t","H","h",
        "S","s","I","i","R","r","J","j","Q","q",
        "K","k","P","p","L","l","O","o","M","m",
        "N","n","0","9","1","8","2","7","3","6",
        "4","5","!","@","#","$","%","^","&","*",
        "(",")","_","-","+","=","~",",",".","<",
        ">","?",":",";","'","[","]","{","}","|");
    return $chars;
}

function wpst_encrypt($value)
{
    return base64_encode(maybe_serialize($value));
}
function wpst_decrypt($value)
{
    return maybe_unserialize(base64_decode($value));
}