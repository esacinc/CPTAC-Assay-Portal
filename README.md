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

* UniProt: [http://www.uniprot.org/](http://www.uniprot.org/)
* Entrez Gene: [http://www.ncbi.nlm.nih.gov/gene](http://www.ncbi.nlm.nih.gov/gene)
* PhoshphoSitePro: [http://www.phosphosite.org/](http://www.phosphosite.org/)

## Administrative Interfaces

### Production

* Application: [https://assays.cancer.gov/authenticate/](https://assays.cancer.gov/authenticate/)

* * *