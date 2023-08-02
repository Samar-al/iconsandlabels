{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
 <div class="icon-card" style="border: 1px solid black; display: flex; width:14rem; margin: 1rem 0 0 1rem;">
    <div class="icon-wrapper" style="padding: 0.5rem">
        <img src="{$iconUrl|escape:'htmlall':'UTF-8'}" alt="Icon" style="width: 50px;">
    </div>
    <div class="content-wrapper">
        <h3>{$title|escape:'htmlall':'UTF-8'}</h3>
        <p>{$description|escape:'htmlall':'UTF-8'}</p>
    </div>
</div>