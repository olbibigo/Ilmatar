<?php
namespace Ilmatar\Doctrine\Extensions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

/**
* "MONTH" "(" SimpleArithmeticExpression ")". Modified from DoctrineExtensions\Query\Mysql\Year
*
* @category DoctrineExtensions
* @package DoctrineExtensions\Query\Mysql
* @author Rafael Kassner <kassner@gmail.com>
* @author Sarjono Mukti Aji <me@simukti.net>
* @license MIT License
*/
class Month extends FunctionNode
{
    public $date;

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return "MONTH(" . $sqlWalker->walkArithmeticPrimary($this->date) . ")";
    }

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->date = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
