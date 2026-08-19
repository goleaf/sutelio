---
paths:
    - '{app/Enums/RememberedEmailStorageStatus.php,app/Services/{EncryptedRememberedEmailStorage,RememberedEmailStorageResult,RememberedEmailStore}.php,app/Listeners/RememberAuthenticatedEmail.php,resources/js/components/auth/**,packages/goleaf/nativephp-email-picker/**}'
---

# Nativephp Email Picker

## Keep native email assistance explicit and permission free

Remember only successful authenticated Sutelio emails in a MAC-authenticated Laravel ciphertext under the app-private sandbox, maximum five MRU entries, and never store passwords or tokens. NativePHP's per-device `APP_KEY` protects the ciphertext key; never send a remembered address through a PHP/native bridge because debug bridge plumbing may log call parameters. Android account access must be user-initiated through the system chooser and return only the selected email; never add GET_ACCOUNTS, READ_CONTACTS, getAccounts(), or cross-app enumeration. Web and iOS must retain manual entry, and logout must not erase the remembered email list.
