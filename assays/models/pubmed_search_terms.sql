SELECT SQL_CALC_FOUND_ROWS
analyte_peptide.peptide_sequence
, CONCAT_WS(', ', protein.gene_symbol, uniprot_gene_synonym, protein.uniprot_protein_name) as search_terms
, CONCAT('https://assays.cancer.gov/',protein.cptac_id) as url
FROM protein
LEFT JOIN analyte_peptide on analyte_peptide.protein_id = protein.protein_id
LEFT JOIN assay_parameters on assay_parameters.analyte_peptide_id = analyte_peptide.analyte_peptide_id
WHERE protein.approval_status = 1