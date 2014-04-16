<?php

/**
 * chdemko\BitArray\BitArray class
 *
 * @author     Christophe Demko <chdemko@gmail.com>
 * @copyright  Copyright (C) 2012-2014 Christophe Demko. All rights reserved.
 *
 * @license    http://www.cecill.info/licences/Licence_CeCILL-B_V1-en.html The CeCILL B license
 *
 * This file is part of the php-bitarray package https://github.com/chdemko/php-bitarray
 */

// Declare chdemko\BitArray namespace
namespace chdemko\BitArray;

/**
 * Array of bits
 *
 * @package  BitArray
 * 
 * @since    1.0.0
 */
class BitArray implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable
{
	/**
	 * @var     string  Underlying data
	 *
	 * @since   1.0.0
	 */
	private $data;

	/**
	 * @var     integer  Size of the bit array
	 *
	 * @since   1.0.0
	 */
	private $size;

	/**
	 * Create a new bit array of the given size
	 *
	 * @param   integer  $size  The BitArray size
	 *
	 * @since   1.0.0
	 */
	protected function __construct($size)
	{
		$this->size = (int) $size;
		$this->data = str_repeat("\0", ceil($this->size / 8));
	}

	/**
	 * Create a new BitArray from an integer
	 *
	 * @param   integer  $size  Size of the bitarray
	 *
	 * @return  BitArray  A new BitArray
	 *
	 * @since   1.0.0
	 */
	public static function fromInteger($size)
	{
		return new BitArray($size);
	}

	/**
	 * Create a new BitArray from an iterable
	 *
	 * @param   iterable  $iterable  An iterable and countable
	 *
	 * @return  BitArray  A new BitArray
	 *
	 * @since   1.0.0
	 */
	public static function fromIterable($iterable)
	{
		$bits = new BitArray(count($iterable));
		$offset = 0;
		$ord = 0;

		foreach ($iterable as $value)
		{
			if ($value)
			{
				$ord |= 1 << $offset % 8;
			}

			if ($offset % 8 === 7)
			{
				$bits->data[(int) ($offset / 8)] = chr($ord);
				$ord = 0;
			}

			$offset++;
		}

		if ($offset % 8 !== 0)
		{
			$bits->data[(int) ($offset / 8)] = chr($ord);
		}

		return $bits;
	}

	/**
	 * Create a new BitArray from a bit string
	 *
	 * @param   string  $string  A bit string
	 *
	 * @return  BitArray  A new BitArray
	 *
	 * @since   1.0.0
	 */
	public static function fromString($string)
	{
		$bits = new BitArray(strlen($string));
		$ord = 0;

		for ($offset = 0; $offset < $bits->size; $offset++)
		{
			if ($string[$offset] !== '0')
			{
				$ord |= 1 << $offset % 8;
			}

			if ($offset % 8 === 7)
			{
				$bits->data[(int) ($offset / 8)] = chr($ord);
				$ord = 0;
			}
		}

		if ($offset % 8 !== 0)
		{
			$bits->data[(int) ($offset / 8)] = chr($ord);
		}

		return $bits;
	}

	/**
	 * Create a new BitArray from json
	 *
	 * @param   string  $json  A json encoded value
	 *
	 * @return  BitArray  A new BitArray
	 *
	 * @since   1.0.0
	 */
	public static function fromJson($json)
	{
		return self::fromIterable(json_decode($json));
	}

	/**
	 * Clone a bitarray
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function __clone()
	{
		$this->data = str_repeat($this->data, 1);
	}

	/**
	 * Test the existence of an index
	 *
	 * @param   integer  $offset  The offset
	 *
	 * @return  boolean  The truth value
	 *
	 * @since   1.0.0
	 */
	public function offsetExists($offset)
	{
		return is_int($offset) && $offset >= 0 && $offset < $this->size;
	}

	/**
	 * Get the truth value for an index
	 *
	 * @param   integer  $offset  The offset
	 *
	 * @return  boolean  The truth value
	 *
	 * @throw   \InvalidArgumentException  Argument index must be an positive integer lesser than the size
	 *
	 * @since   1.0.0
	 */
	public function offsetGet($offset)
	{
		if ($this->offsetExists($offset))
		{
			return (bool) (ord($this->data[(int) ($offset / 8)]) & (1 << $offset % 8));
		}
		else
		{
			throw new \InvalidArgumentException('Argument offset must be a positive integer lesser than the size');
		}
	}

	/**
	 * Set the truth value for an index
	 *
	 * @param   integer  $offset  The offset
	 * @param   boolean  $value   The truth value
	 *
	 * @return  void
	 *
	 * @throw   \InvalidArgumentException  Argument index must be an positive integer lesser than the size
	 *
	 * @since   1.0.0
	 */
	public function offsetSet($offset, $value)
	{
		if ($this->offsetExists($offset))
		{
			$index = (int) ($offset / 8);

			if ($value)
			{
				$this->data[$index] = chr(ord($this->data[$index]) | (1 << $offset % 8));
			}
			else
			{
				$this->data[$index] = chr(ord($this->data[$index]) & ~(1 << $offset % 8));
			}
		}
		else
		{
			throw new \InvalidArgumentException('Argument index must be a positive integer lesser than the size');
		}
	}

	/**
	 * Unset the existence of an index
	 *
	 * @param   integer  $offset  The index
	 *
	 * @return  void
	 *
	 * @throw   \RuntimeException  Values cannot be unset
	 *
	 * @since   1.0.0
	 */
	public function offsetUnset($offset)
	{
		throw new \RuntimeException('Values cannot be unset');
	}

	/**
	 * Return the size
	 *
	 * @return  integer  The size
	 *
	 * @since   1.0.0
	 */
	public function count()
	{
		return $this->size;
	}

	/**
	 * Serialize the object
	 *
	 * @return  array  Array of values
	 *
	 * @since   1.0.0
	 */
	public function jsonSerialize()
	{
		$array = [];

		for ($offset = 0; $offset < $this->size; $offset++)
		{
			$array[] = (bool) (ord($this->data[(int) ($offset / 8)]) & (1 << $offset % 8));
		}

		return $array;
	}

	/**
	 * Convert the object to a string
	 *
	 * @return  string  String representation of this object
	 *
	 * @since   1.0.0
	 */
	public function __toString()
	{
		$string = str_repeat('0', $this->size);

		for ($offset = 0; $offset < $this->size; $offset++)
		{
			if (ord($this->data[(int) ($offset / 8)]) & (1 << $offset % 8))
			{
				$string[$offset] = '1';
			}
		}

		return $string;
	}

	/**
	 * Get an iterator
	 *
	 * @return  Iterator  Iterator
	 *
	 * @since   1.0.0
	 */
	public function getIterator()
	{
		return new Iterator($this);
	}

	/**
	 * Complement the bit array
	 *
	 * @return  BitArray  This object for chaining
	 *
	 * @since   1.0.0
	 */
	public function applyComplement()
	{
		$length = strlen($this->data);

		for ($i = 0; $i < $length; $i++)
		{
			$this->data[$i] = chr(~ ord($this->data[$i]));
		}

		return $this;
	}

	/**
	 * Or with an another bit array
	 *
	 * @param   BitArray  $bits  A bit array
	 *
	 * @return  BitArray  This object for chaining
	 *
	 * @throw   \InvalidArgumentException  Argument must be of equal size
	 *
	 * @since   1.0.0
	 */
	public function applyOr(BitArray $bits)
	{
		if ($this->size == $bits->size)
		{
			$length = strlen($this->data);

			for ($i = 0; $i < $length; $i++)
			{
				$this->data[$i] = chr(ord($this->data[$i]) | ord($bits->data[$i]));
			}

			return $this;
		}
		else
		{
			throw new \InvalidArgumentException('Argument must be of equal size');
		}
	}

	/**
	 * And with an another bit array
	 *
	 * @param   BitArray  $bits  A bit array
	 *
	 * @return  BitArray  This object for chaining
	 *
	 * @throw   \InvalidArgumentException  Argument must be of equal size
	 *
	 * @since   1.0.0
	 */
	public function applyAnd(BitArray $bits)
	{
		if ($this->size == $bits->size)
		{
			$length = strlen($this->data);

			for ($i = 0; $i < $length; $i++)
			{
				$this->data[$i] = chr(ord($this->data[$i]) & ord($bits->data[$i]));
			}

			return $this;
		}
		else
		{
			throw new \InvalidArgumentException('Argument must be of equal size');
		}
	}

	/**
	 * Xor with an another bit array
	 *
	 * @param   BitArray  $bits  A bit array
	 *
	 * @return  BitArray  This object for chaining
	 *
	 * @throw   \InvalidArgumentException  Argument must be of equal size
	 *
	 * @since   1.0.0
	 */
	public function applyXor(BitArray $bits)
	{
		if ($this->size == $bits->size)
		{
			$length = strlen($this->data);

			for ($i = 0; $i < $length; $i++)
			{
				$this->data[$i] = chr(ord($this->data[$i]) ^ ord($bits->data[$i]));
			}

			return $this;
		}
		else
		{
			throw new \InvalidArgumentException('Argument must be of equal size');
		}
	}
}