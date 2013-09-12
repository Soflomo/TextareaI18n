<?php
/**
 * Copyright (c) 2013 Soflomo.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of the
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author      Jurian Sluiman <jurian@soflomo.com>
 * @copyright   2013 Soflomo.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://soflomo.com
 */

namespace Soflomo\TextareaI18n\Entity;

use Locale;

use Soflomo\Textarea\Entity\AbstractText as BaseAbstractText;
use Doctrine\Common\Collections\ArrayCollection;

abstract class AbstractText extends BaseAbstractText
{
    /**
     * List of translations for this text
     *
     * @var ArrayCollection
     */
    protected $translations;

    /**
     * Set the active translation based on locale
     *
     * @var TextTranslation
     */
    protected $activeTranslation;

    /**
     * Locale currently in use
     *
     * @var string
     */
    protected $locale;

    public function __construct()
    {
        $this->translations = new ArrayCollection;
    }

    /**
     * Getter for translations
     *
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Setter for translations
     *
     * @param  ArrayCollection $translations Value to set
     * @return self
     */
    public function setTranslations(ArrayCollection $translations)
    {
        $this->translations = $translations;
        return $this;
    }

    /**
     * Getter for locale
     *
     * @return string
     */
    public function getLocale()
    {
        if (null === $this->locale) {
            $this->locale = $this->detectLocale();
        }

        return $this->locale;
    }

    /**
     * Setter for locale
     *
     * @param  string $locale Value to set
     * @return self
     */
    public function setLocale($locale)
    {
        $translation = $this->getActiveTranslation();
        if ($translation instanceof TextTranslation
         && $locale !== $translation->getLocale()
        ) {
            $this->getTranslation($locale);
        }

        $this->locale = $locale;
        return $this;
    }

    protected function detectLocale()
    {
        return Locale::getDefault();
    }

    protected function getActiveTranslation()
    {
        if (null !== $this->activeTranslation) {
            return $this->activeTranslation;
        }

        $locale = $this->getLocale();
        return $this->getTranslation($locale);
    }

    protected function getTranslation($locale, $setActive = true)
    {
        $translation = null;
        foreach ($this->getTranslations() as $translation) {
            if ($locale === $translation->getLocale()) {
                break;
            }
        }

        if ($translation instanceof TextTranslation && $setActive) {
            $this->setActiveTranslation($translation);
        }
        if ($translation instanceof TextTranslation && $translation->getLocale() !== $this->getLocale()) {
            $this->setLocale($translation->getLocale());
        }
        return $translation;
    }

    protected function setActiveTranslation(TextTranslation $translation)
    {
        $this->activeTranslation = $translation;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getContent()
    {
        return $this->proxyTranslationGet('content');
    }

    /**
     * {@inheritDoc}
     */
    public function setContent($content)
    {
        $this->setLocale($this->detectLocale());
        return $this->proxyTranslationSet('content', $content);
    }

    protected function proxyTranslationGet($property)
    {
        $translation = $this->getActiveTranslation();
        if (null !== $translation) {
            $method = 'get' . ucfirst($property);
            return $translation->$method();
        }
    }

    protected function proxyTranslationSet($property, $value)
    {
        $translation = $this->getActiveTranslation();
        $locale      = $this->getLocale();

        if (!$translation instanceof TextTranslation
         ||  $translation->getLocale() !== $locale
        ) {
            $translation = new TextTranslation;
            $translation->setText($this);
            $translation->setLocale($this->getLocale());

            $this->translations->add($translation);
            $this->setActiveTranslation($translation);
        }

        $method = 'set' . ucfirst($property);
        return $translation->$method($value);
    }
}