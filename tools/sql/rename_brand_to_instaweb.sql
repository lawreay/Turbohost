-- Apply once to an existing installation to update saved platform branding.
UPDATE platform_settings
SET setting_value = 'Instaweb'
WHERE setting_key IN ('site_name', 'footer_brand_name', 'mail_from_name');

UPDATE platform_settings
SET setting_value = 'admin@instaweb.free.dev'
WHERE setting_key = 'site_email';

UPDATE platform_settings
SET setting_value = 'instaweb.free.dev'
WHERE setting_key = 'footer_website';

UPDATE platform_settings
SET setting_value = 'Instaweb - Website Hosting Made Simple'
WHERE setting_key = 'browser_title';

UPDATE platform_settings
SET setting_value = 'Create, upload, and publish websites with Instaweb.'
WHERE setting_key = 'meta_description';