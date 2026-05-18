<?php

namespace App\Services\AddinBuild;

use App\Models\AddinBuild;
use App\Models\AddinConfig;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class AddinBuildService
{
    public function build(AddinConfig $config, int $userId, string $buildType = 'automatic_event'): AddinBuild
    {
        $root = base_path('..');
        $addinPath = $root.DIRECTORY_SEPARATOR.'outlook-addin';
        $storageDir = storage_path('app/addin-builds');
        File::ensureDirectoryExists($storageDir);

        // Generate a fresh manifest ID per build to avoid Outlook-side manifest ID cache collisions.
        $config->manifest_id = (string) Str::uuid();
        $version = $this->nextManifestVersion($config->manifest_version);
        $config->manifest_version = $version;
        $buildToken = now()->format('YmdHis').'-'.Str::lower(Str::random(6));

        $build = AddinBuild::create([
            'config_id' => $config->id,
            'build_type' => $buildType,
            'version' => $version,
            'status' => 'building',
            'created_by' => $userId,
        ]);

        try {
            $manifestContent = $this->renderManifest($config, $buildType);
            $this->assertValidXml($manifestContent);
            $manifestPath = $storageDir.DIRECTORY_SEPARATOR."manifest-{$buildToken}.xml";
            File::put($manifestPath, $manifestContent);

            $packagePath = $storageDir.DIRECTORY_SEPARATOR."outlook-addin-{$buildToken}.zip";

            $zip = new ZipArchive();
            if ($zip->open($packagePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('ZIP olusturulamadi.');
            }

            $zip->addFile($manifestPath, 'manifest.xml');

            $distPath = $addinPath.DIRECTORY_SEPARATOR.'dist';
            if (! File::exists($distPath)) {
                throw new \RuntimeException("Add-in dist klasoru bulunamadi: {$distPath}. Once outlook-addin klasorunde npm run build calistirin.");
            }
            $this->addFolderToZip($zip, $distPath, 'dist');
            $this->publishLatestBuild($manifestContent, $distPath);

            $zip->close();
            $config->save();

            $build->update([
                'status' => 'completed',
                'manifest_path' => $manifestPath,
                'package_path' => $packagePath,
                'notes' => 'Build tamamlandi. Type: '.$buildType,
            ]);
        } catch (\Throwable $e) {
            $build->update([
                'status' => 'failed',
                'notes' => $e->getMessage(),
            ]);
            throw $e;
        }

        return $build;
    }

    private function renderManifest(AddinConfig $config, string $buildType): string
    {
        $id = $config->manifest_id ?: '8c6b669e-4f1d-4fea-8aaf-7dff44f1e4d4';
        $baseTaskpaneUrl = $config->taskpane_url ?: 'https://localhost:5173/index.html';
        $baseAutorunUrl = $config->autorun_url ?: 'https://localhost:5173/autorun.html';
        $version = $config->manifest_version ?: '1.1.0.0';
        $taskpaneUrl = $this->appendVersionQuery($baseTaskpaneUrl, $version);
        $autorunUrl = $this->appendVersionQuery($baseAutorunUrl, $version);
        $origin = $this->buildAppDomain($baseTaskpaneUrl);
        $icon16 = $this->buildAssetUrl($origin, 'icon-16.png');
        $icon = $config->icon_url ?: $this->buildAssetUrl($origin, 'icon-32.png');
        $high = $config->highres_icon_url ?: $this->buildAssetUrl($origin, 'icon-64.png');
        $icon80 = $this->buildAssetUrl($origin, 'icon-80.png');
        $supportUrl = $config->support_url ?: 'https://trinoxmetal.com';
        $providerName = $config->provider_name ?: 'TRINOX';
        $displayName = $config->display_name ?: 'TRINOX Signature Manager';
        $description = $this->buildDescription($version, $buildType);
        $appDomain = $origin;

        // Enable autorun in both manual and automatic manifests.
        $launchEventBlock = "
          <ExtensionPoint xsi:type=\"LaunchEvent\">
            <LaunchEvents><LaunchEvent Type=\"OnNewMessageCompose\" FunctionName=\"onNewMessageComposeHandler\" /></LaunchEvents>
            <SourceLocation resid=\"Autorun.Url\"/>
          </ExtensionPoint>";

        return "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>
<OfficeApp xmlns=\"http://schemas.microsoft.com/office/appforoffice/1.1\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:bt=\"http://schemas.microsoft.com/office/officeappbasictypes/1.0\" xmlns:mailappor=\"http://schemas.microsoft.com/office/mailappversionoverrides\" xsi:type=\"MailApp\">
  <Id>{$this->xml($id)}</Id>
  <Version>{$this->xml($version)}</Version>
  <ProviderName>{$this->xml($providerName)}</ProviderName>
  <DefaultLocale>en-US</DefaultLocale>
  <DisplayName DefaultValue=\"{$this->xml($displayName)}\"/>
  <Description DefaultValue=\"{$this->xml($description)}\"/>
  <IconUrl DefaultValue=\"{$this->xml($icon)}\"/>
  <HighResolutionIconUrl DefaultValue=\"{$this->xml($high)}\"/>
  <SupportUrl DefaultValue=\"{$this->xml($supportUrl)}\"/>
  <AppDomains><AppDomain>{$this->xml($appDomain)}</AppDomain></AppDomains>
  <Hosts><Host Name=\"Mailbox\"/></Hosts>
  <Requirements><Sets><Set Name=\"Mailbox\" MinVersion=\"1.10\"/></Sets></Requirements>
  <FormSettings>
    <Form xsi:type=\"ItemEdit\">
      <DesktopSettings>
        <SourceLocation DefaultValue=\"{$this->xml($taskpaneUrl)}\"/>
      </DesktopSettings>
    </Form>
  </FormSettings>
  <Permissions>ReadWriteItem</Permissions>
  <Rule xsi:type=\"RuleCollection\" Mode=\"Or\"><Rule xsi:type=\"ItemIs\" ItemType=\"Message\" FormType=\"Edit\"/></Rule>
  <VersionOverrides xmlns=\"http://schemas.microsoft.com/office/mailappversionoverrides\" xsi:type=\"VersionOverridesV1_0\">
    <VersionOverrides xmlns=\"http://schemas.microsoft.com/office/mailappversionoverrides/1.1\" xsi:type=\"VersionOverridesV1_1\">
    <Requirements>
      <bt:Sets DefaultMinVersion=\"1.10\">
        <bt:Set Name=\"Mailbox\"/>
      </bt:Sets>
    </Requirements>
    <Hosts>
      <Host xsi:type=\"MailHost\">
        <DesktopFormFactor>
          <FunctionFile resid=\"Autorun.Url\"/>
          <ExtensionPoint xsi:type=\"MessageComposeCommandSurface\">
            <OfficeTab id=\"TabDefault\">
              <Group id=\"trinox.group\">
                <Label resid=\"Group.Label\"/>
                <Control xsi:type=\"Button\" id=\"trinox.openTaskpane\">
                  <Label resid=\"Button.Label\"/>
                  <Supertip>
                    <Title resid=\"Button.Label\"/>
                    <Description resid=\"Button.Tooltip\"/>
                  </Supertip>
                  <Icon><bt:Image size=\"16\" resid=\"Icon.16\"/><bt:Image size=\"32\" resid=\"Icon.32\"/><bt:Image size=\"80\" resid=\"Icon.80\"/></Icon>
                  <Action xsi:type=\"ShowTaskpane\"><SourceLocation resid=\"Taskpane.Url\"/></Action>
                </Control>
              </Group>
            </OfficeTab>
          </ExtensionPoint>
          {$launchEventBlock}
        </DesktopFormFactor>
      </Host>
    </Hosts>
    <Resources>
      <bt:Images><bt:Image id=\"Icon.16\" DefaultValue=\"{$this->xml($icon16)}\"/><bt:Image id=\"Icon.32\" DefaultValue=\"{$this->xml($icon)}\"/><bt:Image id=\"Icon.80\" DefaultValue=\"{$this->xml($icon80)}\"/></bt:Images>
      <bt:Urls><bt:Url id=\"Taskpane.Url\" DefaultValue=\"{$this->xml($taskpaneUrl)}\"/><bt:Url id=\"Autorun.Url\" DefaultValue=\"{$this->xml($autorunUrl)}\"/></bt:Urls>
      <bt:ShortStrings><bt:String id=\"Group.Label\" DefaultValue=\"TRINOX\"/><bt:String id=\"Button.Label\" DefaultValue=\"Signature\"/></bt:ShortStrings>
      <bt:LongStrings><bt:String id=\"Button.Tooltip\" DefaultValue=\"{$this->xml($description)}\"/></bt:LongStrings>
    </Resources>
    </VersionOverrides>
  </VersionOverrides>
</OfficeApp>";
    }

    private function nextManifestVersion(?string $version): string
    {
        if (! $version || ! preg_match('/^\d+\.\d+\.\d+\.\d+$/', $version)) {
            return '1.1.0.1';
        }

        $parts = array_map('intval', explode('.', $version));
        $parts[3]++;

        return implode('.', $parts);
    }

    private function buildDescription(string $version, string $buildType): string
    {
        $label = $buildType === 'automatic_event' ? 'Automatic Outlook compose' : 'Manual Outlook test';
        $builtAt = now()->timezone('Europe/Istanbul')->format('Y-m-d H:i T');

        return "TRINOX centralized email signature manager. Features: auto apply on new mail, manual refresh, API status check, diagnostics. Version {$version}; build: {$label}; created: {$builtAt}.";
    }

    private function publishLatestBuild(string $manifestContent, string $distPath): void
    {
        File::put(public_path('manifest-latest.xml'), $manifestContent);
        File::ensureDirectoryExists(public_path('addin'));
        File::copyDirectory($distPath, public_path('addin'));
    }

    private function buildAppDomain(string $url): string
    {
        $parts = parse_url($url);
        if (! is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return 'https://localhost:5173';
        }

        $origin = $parts['scheme'].'://'.$parts['host'];
        if (! empty($parts['port'])) {
            $origin .= ':'.$parts['port'];
        }

        return $origin;
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function buildAssetUrl(string $origin, string $filename): string
    {
        return rtrim($origin, '/').'/addin/'.$filename;
    }

    private function appendVersionQuery(string $url, string $version): string
    {
        $delimiter = str_contains($url, '?') ? '&' : '?';

        return $url.$delimiter.'v='.rawurlencode($version);
    }

    private function assertValidXml(string $xml): void
    {
        $previous = libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $ok = $dom->loadXML($xml);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($ok) {
            return;
        }

        $first = $errors[0] ?? null;
        $message = $first
            ? sprintf('Manifest XML gecersiz: %s (line %d, col %d)', trim($first->message), $first->line, $first->column)
            : 'Manifest XML gecersiz.';

        throw new \RuntimeException($message);
    }

    private function addFolderToZip(ZipArchive $zip, string $folder, string $prefix): void
    {
        $files = File::allFiles($folder);
        foreach ($files as $file) {
            $relative = $prefix.'/'.str_replace($folder.DIRECTORY_SEPARATOR, '', $file->getPathname());
            $zip->addFile($file->getPathname(), str_replace('\\', '/', $relative));
        }
    }
}
