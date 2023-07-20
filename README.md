# CPTAC Assay Portal

CPTAC ASSAY Portal is a php webapp.
* found that php-curl extention is required
* uses .htaccess to redirect and set global php variables
* must set directory AllowOverride to All in apache virtual host file to enable .htaccess behavior

./core directory includes the Slim and Twig frameworks used in this project.

./swpg_global_settings.php includes db connection properties.


Assay Import Module:
* uses curl calls to parse html in json object for data of interest.

## URLs
* Production: [https://assays.cancer.gov/](https://assays.cancer.gov/)
* Development: [https://assaysdev.cancer.gov/](https://assaysdev.cancer.gov/)
* Staging: [https://cptac.cancer.gov/](https://cptac.cancer.gov/)

## Data Sources
* Panorama: [https://daily.panoramaweb.org/](https://daily.panoramaweb.org/)

```bash
u. halusagn@mail.nih.gov
p. swpgt----t
```

* UniProt: [http://www.uniprot.org/](http://www.uniprot.org/)
* Entrez Gene: [http://www.ncbi.nlm.nih.gov/gene](http://www.ncbi.nlm.nih.gov/gene)
* PhoshphoSitePro: [http://www.phosphosite.org/](http://www.phosphosite.org/)

## Administrative Interfaces

### Production

* Application: [https://assays.cancer.gov/authenticate/](https://assays.cancer.gov/authenticate/)
* WordPress: [http://assays.cancer.gov/cms/wp-login.php](http://assays.cancer.gov/cms/wp-login.php)

### Development
* Application: [https://assaysdev.cancer.gov/authenticate/](https://assaysdev.cancer.gov/authenticate/)
* WordPress: [http://assaysdev.cancer.gov/cms/wp-login.php](http://assaysdev.cancer.gov/cms/wp-login.php)

* * *

## Testing the Import

When an end-user executes an import, it is executed in the background. For this reason, the following URLs are used while testing the import process. Just copy/paste into the browser.

The **"Test Import"** and **"Run Missed Images and Data"** tests directly correspond to functionality available to the end-user in the interface...

(e.g. [https://assaysdev.cancer.gov/assays_import/execute/?import_log_id=1](https://assaysdev.cancer.gov/assays_import/execute/?import_log_id=1))

##### Order of Operations

First, the "import_panorama_protein_peptide.php" script is run:

```bash
/assays_import/controllers/import_panorama_protein_peptide.php
```

Then, the "import_panorama_data.php" script is run:

```bash
/assays_import/controllers/import_panorama_data.php
```

##### Control What Imports are Run

In the second half of the import, you can manually control which imports are run, using the manual override on lines 172-177 found in:

```bash
/assays_import/controllers/import_panorama_data.php
```

*(comment and uncomment as needed, but don't forget to comment it back out before running from the front-end or pushing to production)*

```bash
// Manual override.
$execute['import_chromatogram_images'] = false;
$execute['import_response_curve_images'] = false;
$execute['import_validation_sample_images'] = false;
$execute['import_validation_sample_tabular_data'] = false;
$execute['import_lod_loq_data'] = false;
$execute['import_curve_fit_data'] = false;
```

### FRED_Hutch

#### Full Import

*Note: If you place a die() at the end of this script, it will not move onto the 2nd half of the import. Useful for debugging.*

```bash
https://assaysdev.cancer.gov/assays_import/import_panorama_protein_peptide/?import_log_id=1&account_id=853&uniquehash=537f942e19199o
```

#### Just Images and Data

*The first half (above) needs to be run before running this one.*

```bash
https://assaysdev.cancer.gov/assays_import/import_panorama_data/?import_log_id=1&imports_executed_log_id=1&account_id=853&uniquehash=537f85071eb29
```

#### Test Import (limited to 5)

```bash
https://assaysdev.cancer.gov/assays_import/import_panorama_data/?import_log_id=1&imports_executed_log_id=1&account_id=853&test_import=1&uniquehash=537f85071eb29
```

#### Run Missed Images and Data

*Panorama has the tendancy to be flakey at times, so this will re-run the import against missed images and data.*

```bash
https://assaysdev.cancer.gov/assays_import/import_panorama_data/?import_log_id=1&imports_executed_log_id=1&account_id=853&uniquehash=537f85071eb29&run_missed_images=true
```

* * *

### Broad

#### Full Import

*Note: If you place a die() at the end of this script, it will not move onto the 2nd half of the import. Useful for debugging.*

```bash
https://assaysdev.cancer.gov/assays_import/import_panorama_data/?import_log_id=2&account_id=853&uniquehash=537f85071eb30
```

#### Just Images and Data

*First half (above) needs to be run before running this one.*

```bash
https://assaysdev.cancer.gov/assays_import/import_panorama_data/?import_log_id=2&imports_executed_log_id=2&account_id=853&uniquehash=537f85071eb30
```

#### Test Import (limited to 5)

```bash
https://assaysdev.cancer.gov/assays_import/import_panorama_data/?import_log_id=2&imports_executed_log_id=2&account_id=853&account_id=853&test_import=1&uniquehash=537f85071eb29
```

#### Run Missed Images and Data

*Panorama has the tendancy to be flakey at times, so this will re-run the import against missed images and data.*

```bash
https://assaysdev.cancer.gov/assays_import/import_panorama_data/?import_log_id=2&imports_executed_log_id=2&account_id=853&uniquehash=537f85071eb29&run_missed_images=true
```

* * *

### SNU_KIST

#### Full Import

*Note: If you place a die() at the end of this script, it will not move onto the 2nd half of the import. Useful for debugging.*

```bash
https://assaysdev.cancer.gov/assays_import/import_panorama_data/?import_log_id=3&account_id=853&uniquehash=537f85071eb30
```

#### Just Images and Data

*First half (above) needs to be run before running this one.*

```bash
https://assaysdev.cancer.gov/assays_import/import_panorama_data/?import_log_id=3&imports_executed_log_id=3&account_id=853&uniquehash=537f85071eb30
```

#### Test Import (limited to 5)

```bash
https://assaysdev.cancer.gov/assays_import/import_panorama_data/?import_log_id=3&imports_executed_log_id=3&account_id=853&account_id=853&test_import=1&uniquehash=537f85071eb29
```

#### Run Missed Images and Data

*Panorama has the tendancy to be flakey at times, so this will re-run the import against missed images and data.*

```bash
https://assaysdev.cancer.gov/assays_import/import_panorama_data/?import_log_id=3&imports_executed_log_id=3&account_id=853&uniquehash=537f85071eb29&run_missed_images=true
```

* * *

## History
* Wireframe started: 01/11/2013
* Development started: 03/15/2013