<?php
/**
 * Configuration status banner (collapsible). Shows a small GREEN chip when the
 * module is active/licensed for this domain and an ORANGE chip when it is
 * inactive (suspended / subscription ended / IP-blocked / no valid licence).
 * Lives at the top of Stores > Configuration > Etechflow > Store Locator >
 * License — i.e. RIGHT where the merchant pastes the key — so activation /
 * deactivation is visible at a glance. The big red lock notice (suspended/etc.)
 * is the separate MAIN-page gate; this is only the at-a-glance config chip.
 * Display-only — it never reads or writes the licence key.
 */
declare(strict_types=1);

namespace Etechflow\StoreLocator\Block\Adminhtml\System\Config;

use Etechflow\StoreLocator\Model\LicenseValidator;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class LicenseStatus extends Field
{
    public function __construct(
        Context $context,
        private readonly LicenseValidator $licenseValidator,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Take over the whole config row (no label column / no scope checkbox) so the
     * banner spans full width.
     */
    public function render(AbstractElement $element): string
    {
        return '<tr id="row_' . $element->getHtmlId() . '">'
            . '<td colspan="4" style="padding:8px 0 16px;">' . $this->buildBanner() . '</td>'
            . '</tr>';
    }

    private function buildBanner(): string
    {
        $valid  = false;
        $host   = '';
        $state  = 'none';
        $reason = '';
        try {
            $valid = $this->licenseValidator->isValid();
            $host  = $this->licenseValidator->getCurrentHost();
            if ($valid) {
                $reason = $this->licenseValidator->getUnlockReason();
            } else {
                $state = $this->licenseValidator->getLicenseState();
            }
        } catch (\Throwable $e) {
            // fail soft — show the inactive chip rather than break the config page.
        }

        $id = 'etf-sl-status-detail';
        if ($valid && $reason === 'dev-host') {
            // Unlocked ONLY because this is a development / staging domain — be
            // honest: there is no licence here, and one WILL be needed on production.
            $dot    = '#0b5cad';
            $bg     = '#e8f1fb';
            $border = '#b8d4f0';
            $fg     = '#0b3d73';
            $title  = 'Store Locator is active (development domain)';
            $sub    = $host !== ''
                ? '— unlocked for ' . $this->escapeHtml($host) . ' · no licence required here'
                : '— development domain · no licence required here';
            $detail = 'This is a development / staging domain, so licence enforcement is bypassed and the full module '
                . 'is unlocked <em>without a key</em>. <strong>A valid eTechFlow licence will be required when you go '
                . 'live on your production domain</strong> — until then, any key in the field below is ignored.';
        } elseif ($valid) {
            $dot    = '#1e7e34';
            $bg     = '#eaf7ee';
            $border = '#bfe3c8';
            $fg     = '#155724';
            $title  = 'Store Locator is active';
            $sub    = $host !== '' ? '— licensed for ' . $this->escapeHtml($host) : '— licensed';
            $detail = 'Your licence is valid for this domain. The full module is unlocked — the admin store manager and the storefront map &amp; postcode finder.';
        } else {
            $dot    = '#d39e00';
            $bg     = '#fff8e6';
            $border = '#f3e2b0';
            $fg     = '#856404';
            $title  = 'Store Locator is inactive';
            $reasons = [
                'suspended' => '— licence suspended by the administrator',
                'expired'   => '— subscription ended',
                'blocked'   => '— this server\'s IP is not authorised',
                'none'      => '— no licence key set',
            ];
            $sub = $reasons[$state] ?? '— no valid licence for this domain';
            if (in_array($state, ['suspended', 'expired', 'blocked'], true)) {
                $detail = 'The licence key is kept — the module is only frozen and will re-activate the moment access is restored. Contact <a href="mailto:support@etechflow.com">support@etechflow.com</a>.';
            } else {
                $detail = 'Enter a valid eTechFlow licence key in the field below and click <strong>Save Config</strong> to activate the module.';
            }
        }

        $toggle = "var d=document.getElementById('{$id}');"
            . "var a=this.querySelector('.etf-sl-caret');"
            . "if(d.style.display==='none'){d.style.display='block';a.textContent='▾';}"
            . "else{d.style.display='none';a.textContent='▸';}";

        return '<div style="border:1px solid ' . $border . ';background:' . $bg . ';border-radius:8px;'
            . 'padding:10px 14px;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif;max-width:760px;">'
            . '<div style="cursor:pointer;display:flex;align-items:center;gap:9px;" onclick="' . $toggle . '">'
            . '<span style="color:' . $dot . ';font-size:13px;line-height:1;">&#9679;</span>'
            . '<strong style="color:' . $fg . ';font-size:13px;">' . $title . '</strong>'
            . '<span style="color:' . $fg . ';font-size:12px;">' . $sub . '</span>'
            . '<span class="etf-sl-caret" style="margin-left:auto;color:#6c757d;font-size:12px;">&#9656;</span>'
            . '</div>'
            . '<div id="' . $id . '" style="display:none;margin-top:8px;font-size:12px;line-height:1.5;color:' . $fg . ';">'
            . $detail
            . '</div>'
            . '</div>';
    }
}
