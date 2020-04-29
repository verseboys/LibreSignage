<?php

namespace libresignage\common\php\exportable\diff;

use libresignage\common\php\exportable\diff\BaseDiff;
use libresignage\common\php\exportable\diff\ArrayDiff;
use libresignage\common\php\exportable\diff\EmptyDiff;
use libresignage\common\php\exportable\diff\ObjectDiff;
use libresignage\common\php\exportable\diff\ExportableDiff;
use libresignage\common\php\exportable\Exportable;

/**
* A class with functions for working with diffs between objects/values.
*/
class Diff {
	/**
	* Get a diff between two objects/values.
	*
	* @param $base The base value.
	* @param $other The value compared against $base.
	* @param $depth The maximum recursion depth.
	* @param $private Internal argument.
	*
	* @returns BaseDiff A diff object.
	*/
	public static function diff(
		$base,
		$other,
		int $depth,
		bool $private = FALSE
	): BaseDiff {
		if (!$depth) { return new EmptyDiff($private); }

		if (is_object($base)) {
			if (is_subclass_of($base, Exportable::class)) {
				return new ExportableDiff($base, $other, $depth, $private);
			} else {
				return new ObjectDiff($base, $other, $depth, $private);
			}
		} else if (is_array($base)) {
			return new ArrayDiff( $base, $other, $depth, $private);
		} else {
			return new PrimitiveDiff($base, $other, $private);
		}
	}
}
