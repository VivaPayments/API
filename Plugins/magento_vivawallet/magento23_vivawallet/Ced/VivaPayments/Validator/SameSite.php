<?php
namespace Ced\VivaPayments\Validator;

/**
 * Class SameSite
 * @package Veriteworks\CookieFix\Validator
 */
class SameSite
{

    /**
     * @param $useragent
     * @return bool
     */
    public function shouldSendSameSiteNone($useragent)
    {
        return !$this->isSameSiteNoneIncompatible($useragent);
    }

    /**
     * @param $useragent
     * @return bool
     */
    private function isSameSiteNoneIncompatible($useragent)
    {
        return $this->hasWebKitSameSiteBug($useragent) ||
            $this->dropsUnrecognizedSameSiteCookies($useragent);
    }

    /**
     * @param $useragent
     * @return bool
     */
    private function hasWebKitSameSiteBug($useragent)
    {
        return $this->isIosVersion(12, $useragent) ||
            ($this->isMacosxVersion(10, 14, $useragent) &&
                ($this->isSafari($useragent) || $this->isMacEmbeddedBrowser($useragent)));
    }

    /**
     * @param $useragent
     * @return bool
     */
    private function dropsUnrecognizedSameSiteCookies($useragent) {
        if ($this->isUcBrowser($useragent)) {
            return !$this->isUcBrowserVersionAtLeast(12, 13, 2, $useragent);
        }

        return $this->isChromiumBased($useragent) &&
            $this->isChromiumVersionAtLeast(51, $useragent, '>=') &&
            $this->isChromiumVersionAtLeast(67, $useragent, '<=');
    }

    /**
     * @param $major
     * @param $useragent
     * @return bool
     */
    private function isIosVersion($major, $useragent)
    {
        $regex = "/\(iP.+; CPU .*OS (\d+)[_\d]*.*\) AppleWebKit\//";
        $matched = array();

        if(preg_match($regex, $useragent, $matched)) {
            $version = (int)$matched[1];
            return version_compare($version, $major, '<=');
        }

        return false;
    }

    /**
     * @param $major
     * @param $minor
     * @param $useragent
     * @return bool
     */
    private function isMacosxVersion($major, $minor, $useragent)
    {
        $regex = "/\(Macintosh;.*Mac OS X (\d+)_(\d+)[_\d]*.*\) AppleWebKit\//";
        $matched = array();

        if(preg_match($regex, $useragent, $matched)) {
            return version_compare((int)$matched[1], $major, '=') &&
                version_compare((int)$matched[2], $minor, '<=');
        }

        return false;
    }

    /**
     * @param $useragent
     * @return bool
     */
    private function isSafari($useragent)
    {
        $regex = "/Version\/.* Safari\//";
        return preg_match($regex, $useragent) && !$this->isChromiumBased($useragent);
    }

    /**
     * @param $useragent
     * @return false|int
     */
    private function isMacEmbeddedBrowser($useragent)
    {
        $regex = "/^Mozilla\/[\.\d]+ \(Macintosh;.*Mac OS X [_\d]+\) " .
            "AppleWebKit\/[\.\d]+ \(KHTML, like Gecko\)$/";
        return preg_match($regex, $useragent);
    }

    /**
     * @param $useragent
     * @return false|int
     */
    private function isChromiumBased($useragent)
    {
        $regex = "/Chrom(e|ium)/";
        return preg_match($regex, $useragent);
    }

    /**
     * @param $major
     * @param $useragent
     * @param $operator
     * @return bool|int
     */
    private function isChromiumVersionAtLeast($major, $useragent, $operator)
    {
        $regex = "/Chrom[^ \/]+\/(\d+)[\.\d]* /";
        $matched = array();
        if (preg_match($regex, $useragent, $matched)) {
            $version = (int)$matched[1];
            return version_compare($version, $major, $operator);
        }
        return false;
    }

    /**
     * @param $useragent
     * @return false|int
     */
    private function isUcBrowser($useragent)
    {
        $regex = "/UCBrowser\//";
        return preg_match($regex, $useragent);
    }

    /**
     * @param $major
     * @param $minor
     * @param $build
     * @param $useragent
     * @return bool|int
     */
    private function isUcBrowserVersionAtLeast($major, $minor, $build, $useragent) {
        $regex = "/UCBrowser\/(\d+)\.(\d+)\.(\d+)[\.\d]* /";
        // Extract digits from three capturing groups.
        $matched = array();
        if (preg_match($regex, $useragent, $matched)) {
            $major_version = (int)$matched[1];
            $minor_version = (int)$matched[2];
            $build_version = (int)$matched[3];

            if (version_compare($major_version, $major, '>=')) {
                if (version_compare($minor_version, $minor, '>=')) {
                    return version_compare($build_version, $build, '>=');
                }
            }
        }

        return false;
    }
}