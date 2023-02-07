<?php
/**
 * Created by PhpStorm.
 * User: flavio
 * Date: 12/12/16
 * Time: 09:13
 */

namespace UFT\SluBundle\DoctrineExtensions\Query\Db2;


use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class TiraAcento extends FunctionNode
{
    public $subject = null;
    public $replace = '\'AAAAAaaaaEEEEeeeeIIIiiiiOOOOOooooUUUUuuuu\'';
    public $search = '\'ÁÀÄÃÂáàãâÉÈËÊéèêẽÍÌÏìíĩîÓÒÖÕÔóòôõÚÙÜÛúùũû\'';

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->subject = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        return 'TRANSLATE(' .
        $this->subject->dispatch($sqlWalker) . ' , ' .
        $this->replace . ', ' .
        $this->search .
        ')';
    }


}