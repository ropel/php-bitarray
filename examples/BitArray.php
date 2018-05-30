<?php

/**
 * BitArray example
 *
 * @package    BitArray
 *
 * @author     Christophe Demko <chdemko@gmail.com>
 * @copyright  Copyright (C) 2012-2018 Christophe Demko. All rights reserved.
 *
 * @license    BSD 3-Clause License
 *
 * This file is part of the php-bitarray package https://github.com/chdemko/php-bitarray
 */

//require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/BitArray/BitArray.php';
require __DIR__ . '/../src/BitArray/Iterator.php';

use chdemko\BitArray\BitArray;

// Print 10010
$bits = BitArray::fromString('10010');
echo $bits . PHP_EOL;

// Print 01101
$bits->applyComplement();
echo $bits . PHP_EOL;

// Print 11100
$bits->applyXor(BitArray::fromTraversable(array(true, false, false, false, true)));
echo $bits . PHP_EOL;

// Print 11101
$bits[4] = true;
echo $bits . PHP_EOL;

// Print 0:1;1:1;2:1;3:;4:1;
foreach ($bits as $index => $value)
{
	echo $index . ':' . $value . ';';
}

echo PHP_EOL;

// Print [true,true,true,false,true]
echo json_encode($bits) . PHP_EOL;

// Print 14
$bits = BitArray::fromString('1100000000000010');
echo $bits->nextSetBit(4) . PHP_EOL;

// print 3 to 30
for($i = 2;$i < 30; $i++)
{
	$str = str_pad('10',$i,'0');
	$bits = BitArray::fromString($str . '100');
	echo $str . " -- " . $bits->nextSetBit(2) . PHP_EOL;
}
