# Forms

`dbravoan/dbcms-module-forms` owns form definitions, fields and submissions.
Install/activate `forms`, create the form in the backoffice and configure its
languages, fields and validation. Embed it using `widget_form` with
`form_slug`; the same block works in sections and registered Blade pages.

Public forms preserve CSRF/session state and honeypot protection. Configure the
`forms` processing disclosure, finite retention and contextual privacy notice
before collecting submissions. Optional marketing consent is separate. Do not
cache a final HTML form across visitors or collect unnecessary IP/user-agent data.

Authorized data-right operations use explicit submission IDs:

```bash
./vendor/bin/sail artisan forms:privacy export --id=SUBMISSION_ID --verified
./vendor/bin/sail artisan forms:privacy erase --id=SUBMISSION_ID --verified
./vendor/bin/sail artisan forms:privacy prune
```

`--verified` confirms the operator's prior identity/scope/retention review; it does
not perform that review. Exports are private files; delete them after fulfilling
the request. Pruning uses configured retention and remains relevant for retained
submissions after deactivation. Set a positive `retention_days` on the
`legal.processing` entry whose `owner` is `forms`; expiry starts at submission.
The scheduler runs pruning daily.

See the [extension contract](https://github.com/dbravoan/dbcms/blob/main/docs/EXTENSIONS.md).
