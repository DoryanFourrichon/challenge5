<?php

namespace ACA\WC\Plugin;

use AC;
use AC\Plugin\UpdateCollection;
use ACA\WC\Plugin\Update;

class SetupFactory extends AC\Plugin\SetupFactory {

	public function create( $type ) {

		switch ( $type ) {
			case self::SITE:
				$this->updates = new UpdateCollection( [
					new Update\V3000(),
					new Update\V3300(),
				] );
				break;
		}

		return parent::create( $type );
	}

}