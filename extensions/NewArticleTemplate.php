<?php

$wgHooks['EditPage::showEditForm:initial'][] = array('newArticleTemplates');

function preload($preload) {
	if ( $preload === '' )
		return '';
	else {
		$preloadTitle = Title::newFromText( $preload );
		if ( isset( $preloadTitle ) && $preloadTitle->userCanRead() ) {
			$rev=Revision::newFromTitle($preloadTitle);
			if ( is_object( $rev ) ) {
				$text = $rev->getText();
				// TODO FIXME: AAAAAAAAAAA, this shouldn't be implementing
				// its own mini-parser! -ævar
				$text = preg_replace( '~</?includeonly>~', '', $text );
				return $text;
			} else
				return '';
		}
	}
}

function newArticleTemplates($newPage)
{
	if ( $newPage->mTitle->exists() )
		return;

	global $wgNewArticleTemplatesEnable;
	global $wgNewArticleTemplatesNamespaces;

	$namespace = $newPage->mTitle->getNamespace();

	if ( $wgNewArticleTemplatesEnable && $wgNewArticleTemplatesNamespaces[$namespace] )
	{
		global $wgNewArticleTemplatesDefault;
		global $wgNewArticleTemplates_PerNamespace;
		if ( $wgNewArticleTemplates_PerNamespace[$namespace] )
		{
			$newPage->textbox1 = preload($wgNewArticleTemplates_PerNamespace[$namespace]);
		}
		elseif ( $wgNewArticleTemplatesDefault )
		{
			$newPage->textbox1 = preload($wgNewArticleTemplatesDefault);
		}
	}
}

?>
