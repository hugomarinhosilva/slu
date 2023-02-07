<?php
/**
 * Created by PhpStorm.
 * User: flavio
 * Date: 04/05/16
 * Time: 10:21
 */

namespace UFT\SluBundle\Util;


class Convertores
{

    public function formataCPF($string) {

        return $this->formataCPFComZeros($string);

//        $string = preg_replace('/[^0-9]/', '', $string);
//        return substr($string, 0, 3) . '.' . substr($string, 3, 3) .
//
//        '.' . substr($string, 6, 3) . '-' . substr($string, 9, 2);

    }

    public function formataCPFComZeros($string) {

        $string = preg_replace('/[^0-9]/', '', $string);
        $string = substr(str_pad($string, 11, '0', STR_PAD_LEFT), -11);
        return substr($string, 0, 3) . '.' . substr($string, 3, 3) .

            '.' . substr($string, 6, 3) . '-' . substr($string, 9, 2);

    }

    public function formataTelefoneInternacional($string) {
        if($string==null){
            return null;
        }
        $string = preg_replace('/[^0-9]/', '', $string);
        if(strlen($string)<10){
            return '+5563'.$string;
        }
        elseif(strlen($string)<12){
            return '+55'.$string;
        }else{
            return '+'.$string;
        }
    }

    public function explodeTelefone($string) {
        if($string==null){
            return null;
        }
        $string = preg_replace('/[^0-9]/', '', $string);
        if(strlen($string)<12){
            return array('ddd' => substr($string,0,2),'fone' => substr($string,2,9));
        }
        elseif(strlen($string)<14){
            return array('ddi' => substr($string,0,2),'ddd' => substr($string,2,2),'fone' => substr($string,4,9));
        }
    }


    function tirarAcentos($string){
        return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/"),explode(" ","a A e E i I o O u U n N"),$string);
    }
    function removeAcentos($string, $slug = false) {
        $string = preg_replace("/[áàâãä]/", "a", $string);
        $string = preg_replace("/[ÁÀÂÃÄ]/", "A", $string);
        $string = preg_replace("/[éèê]/", "e", $string);
        $string = preg_replace("/[ÉÈÊ]/", "E", $string);
        $string = preg_replace("/[íì]/", "i", $string);
        $string = preg_replace("/[ÍÌ]/", "I", $string);
        $string = preg_replace("/[óòôõö]/", "o", $string);
        $string = preg_replace("/[ÓÒÔÕÖ]/", "O", $string);
        $string = preg_replace("/[úùü]/", "u", $string);
        $string = preg_replace("/[ÚÙÜ]/", "U", $string);
        $string = preg_replace("/ç/", "c", $string);
        $string = preg_replace("/Ç/", "C", $string);
        $string = preg_replace("/[][><}{)(:;,!?*%~^`&#@]/", "", $string);
        return $string;
    }
}