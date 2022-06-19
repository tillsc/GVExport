<?php
/**
 * functions to build individual and family arrays from the clippings cart
 *
 * Copyright (C) 2022  webtrees development team
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @author Hermann Hartenthaler
 * @license GPL v2 or later
 */

declare(strict_types=1);

namespace vendor\WebtreesModules\gvexport;

use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Media;
use Fisharebest\Webtrees\MediaFile;
use Fisharebest\Webtrees\Session;
use Fisharebest\Webtrees\Site;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Registry;


/**
 * class to read the clippings cart in order to build up the arrays for individuals and families
 *
 */
class functionsClippingsCart {

	// ------------ definition of data structures

	/**
	 * @var $individuals	array as it is defined for GVExport
	 */
	private array $individuals;

	/**
	 * @var $families		array as it is defined for GVExport
	 */
	private array $families;

	private Tree $tree;
	private bool $photoIsRequired;
	private bool $combinedMode;

	// ------------ definition of const

	public const DUMMY_INDIVIDUAL_XREF	= 'I_';
	public const DUMMY_FAMILIY_XREF		= 'F_';						// what happens if someone is using such a XREF ???
	public const HAS_PARENTS			= 'has_parents';
	public const ID_HUSBAND				= 'husb_id';
	public const ID_WIFE				= 'wife_id';
	public const ID_UNKNOWN				= 'unkn_id';

	// ------------ definition of methods

	/**
	 * Constructor for this class
	 *
	 * @param Tree $tree
	 * @param bool $photoIsRequired
	 * @param bool $combinedMode
	 */
	function __construct(Tree $tree, bool $photoIsRequired, bool $combinedMode) {
		$this->tree = $tree;
		$this->photoIsRequired = $photoIsRequired;
		$this->combinedMode = $combinedMode;

		$this->createIndividualsFamiliesListsFromClippingsCart();
	}

	/**
	 * return array individuals as it is defined for GVExport
	 *
	 * @return array
	 */
	public function getIndividuals(): array {
		return $this->individuals;
	}

	/**
	 * return array families as it is defined for GVExport
	 *
	 * @return array
	 */
	public function getFamilies(): array {
		return $this->families;
	}

	/**
	 * read INDI and FAM from the clippings cart and fill the arrays "individuals" and "families"
	 *
	 */
	private function createIndividualsFamiliesListsFromClippingsCart () {
		$records = $this->getRecordsInCart($this->tree);
		$this->addIndividualsFromClippingsCart($records);
		$this->addFamiliesFromClippingsCart($records);

		foreach ($records as $record) {
			if ($record instanceof Individual) {
				$pid = $record->xref();

				// search for a highlighted photo if it is in the clippings cart and if a photo is required
				$this->individuals[$pid]['pic'] = $this->searchPhotoToIndi($pid);

				if ($this->combinedMode) {
					$fams = $record->spouseFamilies();
					if (count($fams) > 0) {
						foreach ($fams as $fam) {
							$fid = $fam->xref();
							$this->individuals[$pid]['fams'][$fid] = $fid;

							if (isset($this->families[$fid]['fid']) && ($this->families[$fid]['fid'] == $fid)) {
								if ($fam->husband() && $fam->husband()->xref() == $pid) {
									$this->families[$fid][self::ID_HUSBAND] = $pid;
								} else {
									$this->families[$fid][self::ID_WIFE] = $pid;
								}
								$this->families[$fid][self::HAS_PARENTS] = true;
							}
						}
					} else {
						// If there is no spouse family we create a dummy one
						$this->individuals[$pid]['fams'][self::DUMMY_FAMILIY_XREF.$pid] = self::DUMMY_FAMILIY_XREF.$pid;
						$this->addFamToList(self::DUMMY_FAMILIY_XREF.$pid);
						$this->families[self::DUMMY_FAMILIY_XREF.$pid][self::HAS_PARENTS] = true;

						if ($record->sex() == "M") {
							$this->families[self::DUMMY_FAMILIY_XREF.$pid][self::ID_HUSBAND] = $pid;
							$this->families[self::DUMMY_FAMILIY_XREF.$pid][self::ID_WIFE] = "";
						} elseif ($record->sex() == "F") {
							$this->families[self::DUMMY_FAMILIY_XREF.$pid][self::ID_WIFE] = $pid;
							$this->families[self::DUMMY_FAMILIY_XREF.$pid][self::ID_HUSBAND] = "";
						} else {
							// Unknown or other gender
							// tbd: add code for "other"
							$this->families[self::DUMMY_FAMILIY_XREF.$pid][self::ID_UNKNOWN] = $pid;
							$this->families[self::DUMMY_FAMILIY_XREF.$pid][self::ID_WIFE] = "";
							$this->families[self::DUMMY_FAMILIY_XREF.$pid][self::ID_HUSBAND] = "";
						}
					}
				}
			} elseif ($record instanceof Family) {
				if ($this->combinedMode) {
					$fid = $record->xref();
					$husband = $record->husband();
					if (isset($husband) && !in_array($husband->xref(), $this->getXrefsInCart($this->tree))) {
						// if there is no husband we create a dummy one
						$pid = self::DUMMY_INDIVIDUAL_XREF.'H'.$fid;
						$this->addIndiToList($pid);
                        $this->individuals[$pid]['rel'] = false;
						$this->individuals[$pid]['fams'][$fid] = $fid;
						$this->families[$fid][self::ID_HUSBAND] = $pid;
						$this->families[$fid][self::HAS_PARENTS] = true;
					}
					$wife = $record->wife();
					if (isset($wife) && !in_array($wife->xref(), $this->getXrefsInCart($this->tree))) {
						// if there is no wife we create a dummy one
						$pid = self::DUMMY_INDIVIDUAL_XREF.'W'.$fid;
						$this->addIndiToList($pid);
                        $this->individuals[$pid]['rel'] = false;
						$this->individuals[$pid]['fams'][$fid] = $fid;
						$this->families[$fid][self::ID_WIFE] = $pid;
						$this->families[$fid][self::HAS_PARENTS] = true;
					}
				}
			}
		}
	}

	/**
	 * read INDI records from the clippings cart and initially fill the array "individuals"
	 *
	 * @param array $records
	 */
	private function addIndividualsFromClippingsCart (array $records)
	{
		foreach ($records as $record) {
			if ($record instanceof Individual) {
				$pid = $record->xref();
				$this->addIndiToList($pid);
                $this->individuals[$pid]['rel'] = true;
			}
		}
	}

	/**
	 * read FAM records from the clippings cart and initially fill the array "families"
	 *
	 * @param array $records
	 */
	private function addFamiliesFromClippingsCart (array $records)
	{
		foreach ($records as $record) {
			if ($record instanceof Family) {
				$fid = $record->xref();
				$this->addFamToList($fid);
			}
		}
	}

	/**
	 * Adds a individual to the individuals list
	 *
	 * @param string $pid XREF of this individual
	 */
	private function addIndiToList(string $pid) {
		if(!isset($this->individuals[$pid])) {
			$this->individuals[$pid] = array();
		}
		$this->individuals[$pid]['pid'] = $pid;
	}

	/**
	 * Adds a family to the family list
	 *
	 * @param string $fid XREF of this family
	 */
	private function addFamToList(string $fid) {
		if(!isset($this->families[$fid])) {
			$this->families[$fid] = array();
		}
		$this->families[$fid]['fid'] = $fid;
	}

	/**
	 * check if clippings cart of a tree is empty
	 *
	 * @param Tree $tree
	 * @return bool
	 */
	public static function isCartEmpty(Tree $tree): bool
	{
		$cart     = Session::get('cart', []);
		$contents = $cart[$tree->name()] ?? [];

		return $contents === [];
	}

	/**
	 * Get the XREFs in the clippings cart.
	 *
	 * @param Tree $tree
	 *
	 * @return array
	 */
	private function getXrefsInCart(Tree $tree): array
	{
		$cart = Session::get('cart', []);
		$xrefs = array_keys($cart[$tree->name()] ?? []);
		$xrefs = array_map('strval', $xrefs);           			// PHP converts numeric keys to integers
		return $xrefs;
	}

	/**
	 * Get the records in the clippings cart.
	 *
	 * @param Tree $tree
	 *
	 * @return array
	 */
	private function getRecordsInCart(Tree $tree): array
	{
		$xrefs = $this->getXrefsInCart($tree);
		$records = array_map(static function (string $xref) use ($tree): ?GedcomRecord {
			return Registry::gedcomRecordFactory()->make($xref, $tree);
		}, $xrefs);

		// some records may have been deleted after they were added to the cart, remove them
		$records = array_filter($records);

		// group and sort the records
		uasort($records, static function (GedcomRecord $x, GedcomRecord $y): int {
			return $x->tag() <=> $y->tag() ?: GedcomRecord::nameComparator()($x, $y);
		});

		return $records;
	}

	/**
	 * Adds a path to the highlighted photo of a given individual
	 * if it is in the clippings cart
	 * and if the parameter defines that photos are required
	 *
	 * @param string $pid XREF of individual
	 * @return string|null URL of highlighted media file (rendering in brpowser) or
	 * 					   file location in media folder (rendering on server) or
	 * 					   null
	 */
	private function searchPhotoToIndi(string $pid): ?string
	{
		if ($this->photoIsRequired) {
			$mediaFile = $this->preferedPhotoInCart(Registry::individualFactory()->make($pid, $this->tree));
			if (isset($mediaFile) && !$mediaFile->isExternal()) {
				// If we are rendering in the browser, provide the URL, otherwise provide the server side file location
				if (isset($_REQUEST["render"])) {
					return Site::getPreference('INDEX_DIRECTORY') . $this->tree->getPreference('MEDIA_DIRECTORY') . $mediaFile->filename();
				} else {
					return str_replace("&", "%26", $mediaFile->imageUrl(200, 200, "contain"));
				}
			}
		}
		return null;
	}

	/**
	 * find a highlighted media file for an individual;
	 * the media object has to be in the clippings cart
	 *
	 * @param Individual $individual
	 * @return MediaFile|null
	 */
	private function preferedPhotoInCart(Individual $individual): ?MediaFile
	{
		$fact = $individual->facts(['OBJE'])
			->first(static function (Fact $fact): bool {
				$media = $fact->target();

				return $media instanceof Media && $media->firstImageFile() instanceof MediaFile;
			});

		if ($fact instanceof Fact && $fact->target() instanceof Media && $this->isXrefInCart($fact->target()->xref())) {
			return $fact->target()->firstImageFile();
		}

		return null;
	}

	/**
	 * check if the XREF of a MediaObject is an element in the clippings cart
	 *
	 * @param string $xref
	 * @return bool
	 */
	private function isXrefInCart(string $xref): bool
	{
		return in_array($xref, $this->getXrefsInCart($this->tree), true);
	}
}
?>
