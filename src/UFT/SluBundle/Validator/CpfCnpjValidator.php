<?php
namespace UFT\SluBundle\Validator;

/**
 * Validar se o cpf ou cnpj é válido
 * @see http://www.geradorcpf.com/algoritmo_do_cpf.htm
 * @see http://www.geradorcnpj.com/algoritmo_do_cnpj.htm
 *
 */
class CpfCnpjValidator
{
    public function validador($valor)
    {
        $constraint = array('cpf'=>false,'cnpj'=>false);
        $valor = preg_replace('/[^0-9]/', '', $valor);
        if(empty($valor))
            return false;
        //Verificando se há números repetidos como: 0000000000
        for($i = 0; $i <= 9; $i++) {
            $repetidos = str_pad('', strlen($valor), $i);
            if($valor === $repetidos)
                return false;
        }
        if(strlen($valor) !== 11 && strlen($valor) !== 14) {
                return false;
        } else {
            // Verifica se é CPF
            if (strlen($valor) === 11) {
                $constraint['cpf'] = true;
            } else if (strlen($valor) === 14) {// Verifica se é CNPJ
                $constraint['cnpj'] = true;
            }
        }
        if ($constraint['cpf'] && $valor == "12345678909")
            return false;
        $pesos = $constraint['cnpj'] ? 6 : 11;

        //Para o CPF serão os pesos 10 e 11
        //Para o CNPJ serão os pesos 5 e 6
        for($peso = ($pesos - 1), $digito = (strlen($valor) - 2); $peso <= $pesos; $peso++, $digito++) {
            for($soma = 0, $i = 0, $posicao = $peso; $posicao >= 2; $posicao--, $i++) {
                $soma = $soma + ($valor[$i] * $posicao);

                // Parte específica para CNPJ Ex.: 5-4-3-2-9-8-7-6-5-4-3-2
                if($constraint['cnpj'] && $posicao < 3 && $i < 5)
                    $posicao = 10;
            }
            $soma = ((10 * $soma) % 11) % 10;

            if ($valor{$digito} != $soma) {
                return false;
            }

        }

        return true;
    }
}