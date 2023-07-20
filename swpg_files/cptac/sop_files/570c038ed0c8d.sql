select * from `group`

select * from account where given_name = 'Kristen'

select * from laboratories where laboratories_id = 7

select * from import_log

update import_log 
set laboratory_id = 9
where import_log_id = 15



insert into laboratories(group_id,laboratory_name,laboratory_abbreviation,primary_contact_name,primary_contact_email_address)
values (9,'Washington University in St. Louis','WUSTL_Townsend','Reid Townsend','rtownsend@wustl.edu')

select * from import_log

update import_log set laboratory_id = 9 where import_log_id = 16

select distinct panorama_peptide_url, peptide_sequence, last_modified, import_log_id from analyte_peptide 
group by panorama_peptide_url
where peptide_sequence = "DGVVEITGK"

select * from analyte_peptide where protein_id IN (1044,1061,1078,1095,1112)

import_log_id = 18

select * from protein where import_log_id IN (15,16,19,18,17)


select analyte_peptide_id, sequence, count(sequence) from panorama_validation_sample_images where 
import_log_id = 
group by sequence


select * from protein where approval_status = 0 and import_log_id = 12

update protein 
set approval_status = 1
where gene_symbol = 'IKBKG' and protein_id = 805

SELECT DISTINCT(gene_symbol), uniprot_accession_id FROM protein


select * from import_initial_start_records where import_log_id = 16

select * from panorama_response_curve_images where import_log_id > 5




SELECT
      protein.protein_id as manage
      , protein.gene_symbol as gene
      , protein.uniprot_accession_id as uniprot
      , protein.uniprot_accession_id as uniprot_ac
      , CONCAT('http://www.uniprot.org/uniprot/',protein.uniprot_accession_id ) as uniprot_link
      , protein.chromosome_number
      , protein.chromosome_start
      , protein.chromosome_stop
      , protein.uniprot_gene_synonym
      , protein.uniprot_hgnc_gene_id
      , protein.uniprot_kb
      , protein.uniprot_source_taxon_id
 
      , protein.uniprot_sequence_length
      , protein.uniprot_protein_name
      , protein.protein_molecular_weight as protein_molecular_weight
      , protein.approval_status 
     
      , analyte_peptide.peptide_sequence as peptide_sequence
      , analyte_peptide.peptide_modified_sequence as peptide_modified_sequence
      , analyte_peptide.peptide_start as peptide_start
      , analyte_peptide.peptide_end as peptide_end
      , analyte_peptide.peptide_molecular_weight as peptide_molecular_weight
      , analyte_peptide.modification_type as modification
      , analyte_peptide.cptc_catalog_id as cptc_catalog_id
      , assay_parameters_new.matrix
      , assay_parameters_new.data_type
      , assay_parameters_new.enrichment_method
      , assay_parameters_new.fractionation_approach
      , assay_types.label as assay_type
      FROM analyte_peptide
      LEFT JOIN protein on analyte_peptide.protein_id = protein.protein_id 
      LEFT JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
      LEFT JOIN assay_types on assay_types.assay_types_id = assay_parameters_new.assay_types_id
      WHERE 

SELECT *
        FROM panorama_response_curve_images 
        
        WHERE  laboratory_id = 12
        WHERE sequence = 'LAAPSVSHVS[+80.0]PR'
        AND analyte_peptide_id = 649
        AND laboratory_id = 10;
        
SELECT *
        FROM panorama_response_curve_images 
        
        WHERE  laboratory_id = 12
        and sequence = 'LAAPSVSHVS[+80.0]PR'
        AND analyte_peptide_id = 1097        

select * from panorama_response_curve_images        
        





import_log_id = 11

where import_log_id = 58

select * from import_log

select * from `group`

select * from laboratories

select * from assay_parameters_new    

select * from publications    

where import_log_id = 16

select p1.gene_symbol as 'Official Gene Symbol',
ap1.peptide_sequence as 'Peptide Sequence',
ap1.peptide_modified_sequence as 'Peptide Modified Sequence',
p1.cptac_id as 'CPTAC ID',
ap1.modification_type as 'Modification Type',
ap1.site_of_modification_peptide as 'Site of Modification',
ap1.peptide_start as 'Peptide start',
ap1.peptide_end as 'Peptide end',
ap1.peptide_molecular_weight as 'Peptide Molecular Mass',
apn.protein_species_label as 'Species',
at1.label as 'Assay',
apn.data_type as 'Type',
apn.enrichment_method as 'Enrichment Method',
apn.matrix as 'Matrix',
l1.laboratory_name as 'Submitting Lab',
l1.primary_contact_name as 'Submitting Lab PI'
from protein as p1 
left join analyte_peptide as ap1 on ap1.protein_id = p1.protein_id
left join assay_parameters_new as apn on apn.import_log_id = ap1.import_log_id
left join import_log as imp on imp.import_log_id = apn.import_log_id
left join laboratories as l1 on imp.laboratory_id = l1.group_id 
left join publications as pub on pub.import_log_id = imp.import_log_id
left join assay_types as at1 on at1.assay_types_id = apn.assay_types_id
where p1.approval_status = 1  and imp.import_log_id = '12'

select * from protein where import_log_id in (12,15) and approval_status = 2

update protein 
set approval_status = 2
where import_log_id = 12 and approval_status = 0

select * from import_log

update import_log set laboratory_id = 9 
where import_log_id = 15



select * from account where given_name = 'Kristen'

select * from user_account_groups where account_id = 75216

select * from user_account_groups where account_id = 65169

select * from analyte_peptide where import_log_id = 10

select * from protein where import_log_id = 13

approval_moderation_notes

select * from analyte_peptide where import_log_id = 14

select * from assay_parameters_new 

select * from sop_files_join 

select * from analyte_peptide where protein_id = 991

select * from assay_parameters_new where protein_species_label = "Human";

where modification_type != 'unmodified' and import_log_id >10

import_log_id = 14

select * from protein where import_log_id = 14 and approval_status = 0

select * from assay_types

where protein_id > 900

select * from import_log 

select * from account where given_name = 'Jeff'

select * from user_account_groups where account_id = 75210

select * from import_initial_start_records where import_log_id = 20

select * from user_account_roles

select * from `group`

insert into user_account_groups (role_id, group_id, account_id) 
VALUES (4,9,75215);

select * from import_initial_start_records where import_log_id = 24

import_log_id = 14

select * from protein where protein_id IN (1025,1030,1029,1000,999)

select gene_symbol, protein_id, peptide_start, peptide_end, 
peptide_sequence from analyte_peptide where protein_id IN (1025,1030,1029,1000)



select * from protein where import_log_id = 14 and cptac_id = 'CPTAC-1025'

select * from sop_files s1 
left join sop_files_join sj1 on sj1.sop_files_id = s1.sop_files_id
where sj1.import_log_id IN (13,14) 
and s1.sop_files_id = 101

select * from protein where protein_id = 916

p
inner join analyte_peptide ap1 on ap1.protein_id = p.protein_id 
and p.cptac_id = "CPTAC-815"

select * from protein p1 inner join analyte_peptide ap1 
on ap1.protein_id = p.protein_id where cptac_id='CPTAC-82'

select * from import_log

update import_log 
set laboratory_id = 9
where import_log_id = 29

select * from panorama_chromatogram_images where import_log_id = 15

select * from panorama_validation_sample_images where 
sequence = 'LSASSEDISER'
and import_log_id = 15

update panorama_chromatogram_images set file_name='AIGNNSATSPR_precursor_chrom_id_7912499_WUSTL_Townsend.png' where import_log_id =29 and file_name='AIGNNSATSPR_precursor_chrom_id_7912499_WUSTL_Townsend_CellLysate_5600TripleTOF_directPRM2_15.png'

select * from panorama_response_curve_images where import_log_id = 15

select * from analyte_peptide where import_log_id = 20

select * from protein where uniprot_accession_id =  'P18615'

select * from protein where import_log_id = 20 

select * from import_initial_start_records where import_log_id = 31


update protein 
set uniprot_accession_id = 'P18615'
where protein_id = 999

select * from protein where protein_id = 999

select * from kegg_uniprot_map where uniprot_accession_id = 'P51587'
select * from uniprot_splice_junctions where uniprot_accession_id = 'P51587'
select * from uniprot_snps where uniprot_accession_id = 'P51587'

select * from uniprot_isoforms where 
uniprot_accession_id = 'B4DYX9'

select * from uniprot_splice_junctions where 
uniprot_accession_id = 'B4DYX9'

select * from uniprot_snps where 
uniprot_accession_id = 'B4DYX9'

select * from analyte_peptide where protein_id = 82

select * from protein where protein_id = 82

select * from uniprot_splice_junctions where 
uniprot_accession_id = 'Q96CM8'  

update uniprot_splice_junctions 
set uniprot_accession_id = 'P18615'
where uniprot_accession_id = 'B4DYX9'

update uniprot_snps
set uniprot_accession_id = 'P18615'
where uniprot_accession_id = 'B4DYX9'

select * from protein where protein_id = 999

protein_id = 991

select * from analyte_peptide where protein_id = 793

select * from analyte_peptide where import_log_id = 16

protein_id = 564

select * from panorama_validation_sample_images where sequence = 'EC[+57.0]QALEGR'

analyte_peptide_id = 1047


where sequence = 'LLIIDSNLGVQDVENLK'
and validation_sample_images_id in (1046,1063)


select * from protein where import_log_id = 15 and approval_status = 1

approval_status = 1


where uniprot_accession_id = '793'

select * from `group`

select * from import_log

update import_log set laboratory_id = 13 where import_log_id = 20

select * from account where username = 'cptac_reviewer'
select * from account where username = 'nycek'

select * from user_account where account_id = 75210

select * from account_type

select * from `group`

  SELECT DISTINCT 
          group_closure_table.descendant as laboratories_id
        , group.name as laboratory_name
      FROM group_closure_table
      LEFT JOIN `group` ON group.group_id = group_closure_table.descendant
      LEFT JOIN `import_log` ON import_log.laboratory_id = group.group_id
      LEFT JOIN `protein` ON protein.import_log_id = import_log.import_log_id
      WHERE group_closure_table.ancestor = 6
      AND group_closure_table.pathlength = 1
      -- AND protein.approval_status = 1
      AND group.group_id = 13
      

select * from uniprot_splice_junctions where uniprot_accession_id = 'P38398'

select * from `group`

select * from laboratories

update import_log
set laboratory_id = 15
where import_log_id = 31

select * from user_account_groups where account_id =75210

insert into user_account_groups (role_id,group_id,account_id) 
values (4,14,75210)

select * from user_account_roles

select * from assay_parameters_new where import_log_id = 9

select (select Column_name 
from Information_schema.columns 
where Table_name like 'assay_parameters_new' and Column_name != 'assay_parameters_id' and ) from 
assay_parameters_new
where import_log_id = 9

select affinity_reagent_type
,antibody_portal_url
,antibody_vendor
,assay_types_id
,celllysate_path
,column_dimensions
,column_material
,column_packing
,column_temperature
,conditions
,data_type
,detected_in_what_sample
,endogenous_amount_concentration
,endogenous_detected
,enrichment_method
,flow_rate
,fraction_combination_strategy
,fractionation_approach
,gradient_description
,import_log_id
,instrument
,internal_standard
,last_modified
,lc
,matrix
,matrix_amount_and_units
,media
,mobile_phase_a
,mobile_phase_b
,number_of_fractions_analyzed
,number_of_fractions_collected
,peptide_standard_purity
,peptide_standard_purity_types_id
,protein_species_label
,quantification_units
,separation_conditions







select * from protein where protein_id = 82

select g1.abbreviation as folder_name, a1.celllysate_path as celllysate_path, p1.cptac_id, p1.approval_status from protein p1
inner join import_log i1 on p1.import_log_id = i1.import_log_id
inner join `group` g1 on i1.laboratory_id = g1.group_id
inner join assay_parameters_new as a1 on a1.import_log_id = i1.import_log_id

select * from `group`

order by approval_status

select * from assay_parameters_new where import_log_id = 1

select * from import_log 

select * from assay_types

select * from page_load

select * from user_account_groups where account_id = 75210

select * from user_account_roles

select * from analyte_peptide where import_log_id = 15

delete from user_account_groups where user_account_groups_id = 369

select * from account where account_id=75210

insert into user_account_groups (role_id, group_id, account_id) 
VALUES (4,6,75210);

select 4,group_id,75217
from `group`;


select * from analyte_peptide where import_log_id <= 11

where gene_symbol = '' and import_log_id = 65

select * from kegg_uniprot_map

select * from import_initial_start_records 

select * from uniprot_isoforms where uniprot_accession_id = 'B4DQM4'



CREATE TABLE uniprot_isoform_splice_data
(
ID int NOT NULL AUTO_INCREMENT,
UNIPROT_ISOFORMS_ID int NOT NULL,
ISOFORM_NAME varchar(255) NOT NULL,
START INT(10),
END varchar(10),
POSITION varchar(10),
PRIMARY KEY (ID),
FOREIGN KEY (UNIPROT_ISOFORMS_ID) REFERENCES UNIPROT_ISOFORMS(UNIPROT_ISOFORMS_ID)
)

ALTER TABLE uniprot_isoform_splice_data
ADD insertion varchar(10)


insert into uniprot_isoform_splice_data (UNIPROT_ISOFORMS_ID,ISOFORM_NAME,`START`,`END`)
values(988,'P38398-7',1,1863)

select id, sequence_length from uniprot_isoforms where uniprot_accession_id = 'P38398'

select * from uniprot_isoform_splice_data

SELECT u.id, u.note, u.sequence_length,ud.`start`,ud.`end`,ud.`position`
FROM uniprot_isoforms u
left join uniprot_isoform_splice_data ud on ud.UNIPROT_ISOFORMS_ID = u.uniprot_isoforms_id
WHERE u.uniprot_accession_id = 'P38398'
order by ud.UNIPROT_ISOFORMS_ID DESC

update uniprot_isoform_splice_data
set end = 1884
where id = 11

select * from uniprot_isoform_splice_data

delete from uniprot_isoform_splice_data
where id = 8



_splice_data where 

select * from kegg_uniprot_map
where uniprot_accession_id LIKE 'Q15717'

select * from lod_loq_comparison 
where import_log_id = 14

SELECT id, sequence, note, sequence_length
        FROM uniprot_isoforms
        WHERE uniprot_accession_id = 'Q14790'

select uniprot_accession_id, count(id) from uniprot_isoforms 
group by uniprot_accession_id 

select * from protein where uniprot_accession_id = 'Q14790'

where uniprot_accession_id = 'O43707'

select * from uniprot_snps where uniprot_accession_id = 'P42771'

select * from uniprot_splice_junctions where uniprot_accession_id = 'B9A041'

select * from protein where protein_id = 916

update protein set uniprot_accession_id = 'Q5SSJ5' where protein_id = 82

ORDER by created_date DESC



select * from panorama_chromatogram_images where import_log_id = 14
where import_log_id = 10

select * from response_curves_data where import_log_id = 14
where import_log_id = 11

ORDER by created_date DESC

select * from laboratories


select * from `group`


select * from laboratories 

select * from import_log where import_log_id > 30

select * from protein as p1 
left join analyte_peptide as ap1 on p1.protein_id = ap1.protein_id
left join import_log 

select asn.celllysate_path, im.import_log_id, g1.abbreviation from assay_parameters_new as asn
left join import_log as im on asn.import_log_id = im.import_log_id
left join `group` as g1 on im.laboratory_id = g1.group_id
where im.import_log_id < 13
  
select * from protein where protein_id in (916,917)

select * from analyte_peptide where protein_id in (916,917)

select * from import_initial_start_records



select * from analyte_peptide 

where import_log_id = 13

select * from analyte_peptide where import_log_id = 14

select * from analyte_peptide where protein_id = 916

select * from protein where protein_name is not NULL

update protein 
set uniprot_accession_id = 'Q9HCN4'
where protein_id = 1027

select * from uniprot_isoforms where uniprot_accession_id = 'Q9HCN4'

select * from uniprot_snps where uniprot_accession_id = 'Q9HCN4'

select * from uniprot_splice_junctions where uniprot_accession_id = 'Q9HCN4'

1 and protein_id = 2

select * from import_initial_start_records where import_log_id = 13

select cptac_id,gene_symbol, uniprot_accession_id from protein
where protein_id not in (916,925,936,960,1022)

select * from protein where uniprot_protein_name = 'Array'

protein_id in (7,8)

update protein 
set uniprot_protein_name = 'Alanine--tRNA ligase, cytoplasmic'
where protein_id in (1,2);
update protein 
set uniprot_protein_name = 'Alpha-actinin-4'
where protein_id in (7,8);
update protein 
set uniprot_protein_name = 'NAD(P)H-hydrate epimerase'
where protein_id in (25,26);
update protein 
set uniprot_protein_name = 'Platelet-activating factor acetylhydrolase IB subunit alpha'
where protein_id in (103,104);
update protein 
set uniprot_protein_name = 'Adenylosuccinate synthetase isozyme 2'
where protein_id in (163,164);
update protein 
set uniprot_protein_name = 'ATP-dependent 6-phosphofructokinase, platelet type'
where protein_id in (251,252);
update protein 
set uniprot_protein_name = 'Cyclin-dependent kinase inhibitor 2A'
where protein_id in (333,334);
update protein 
set uniprot_protein_name = 'ATP-dependent 6-phosphofructokinase, liver type'
where protein_id in (425,426);
update protein 
set uniprot_protein_name = 'Nucleolar RNA helicase 2'
where protein_id in (494,495);
update protein 
set uniprot_protein_name = 'Pre-mRNA-processing factor 19'
where protein_id in (576,577);
update protein 
set uniprot_protein_name = 'Flap endonuclease 1'
where protein_id = 700;

select * from import_log








select * from import_log


where import_log_id = 11
and panorama_peptide_url = '/labkey/targetedms/CPTAC%20Assay%20Portal/FHCRC_Paulovich/CellLysate_5500QTRAP_directMRM/ResponseCurve/showPeptide.view?id='

select * from analyte_peptide where protein_id = 90

where peptide_sequence = 'SGGGAGSNGSVLDPAER'

select * from protein where approval_status = 0

SELECT
        protein.protein_id as manage
      , protein.gene_symbol as gene
      , protein.uniprot_accession_id as uniprot
      , analyte_peptide.analyte_peptide_id
      , analyte_peptide.peptide_sequence
      , analyte_peptide.peptide_modified_sequence
      , analyte_peptide.peptide_start
      , analyte_peptide.peptide_end
      , analyte_peptide.peptide_molecular_weight
      , analyte_peptide.modification_type as modification
      , analyte_peptide.site_of_modification_peptide
      , analyte_peptide.panorama_peptide_url
      , analyte_peptide.panorama_protein_url
      , analyte_peptide.peptide_standard_label_type
      , analyte_peptide.cptc_catalog_id as cptc_catalog_id
      , assay_parameters_new.instrument
      , assay_parameters_new.internal_standard     
      , peptide_standard_purity_types.type as peptide_standard_purity
      , peptide_standard_purity_types.peptide_standard_purity_types_id
      , assay_parameters_new.lc
      , assay_parameters_new.column_packing
      , assay_parameters_new.column_dimensions
      , assay_parameters_new.flow_rate
      , assay_parameters_new.matrix
      , assay_parameters_new.protein_species_label as species
      , assay_parameters_new.celllysate_path
      , assay_types.label as assay_type
      , group.group_id as laboratories_id
      , group.name as laboratory_name
      , group.abbreviation as laboratory_abbreviation
      , group.primary_contact_name
      , group.primary_contact_email_address
      , group.disclaimer
      , publications.publication_citation
      , publications.publication_url
      , import_log.import_log_id
      FROM analyte_peptide
      LEFT JOIN protein on analyte_peptide.protein_id = protein.protein_id 
      LEFT JOIN import_log ON import_log.import_log_id = protein.import_log_id
      LEFT JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
      LEFT JOIN peptide_standard_purity_types ON peptide_standard_purity_types.peptide_standard_purity_types_id = assay_parameters_new.peptide_standard_purity_types_id
      LEFT JOIN `group` ON import_log.laboratory_id = group.group_id
      LEFT JOIN assay_types on assay_types.assay_types_id = assay_parameters_new.assay_types_id
      LEFT JOIN publications ON publications.import_log_id = import_log.import_log_id
      WHERE protein.gene_symbol = 'LARP1B'























SELECT
        protein.protein_id as manage
      , protein.gene_symbol as gene
      , protein.uniprot_accession_id as uniprot
      , analyte_peptide.analyte_peptide_id
      , analyte_peptide.peptide_sequence
      , analyte_peptide.peptide_modified_sequence
      , analyte_peptide.peptide_start
      , analyte_peptide.peptide_end
      , analyte_peptide.peptide_molecular_weight
      , analyte_peptide.modification_type as modification
      , analyte_peptide.site_of_modification_peptide
      , analyte_peptide.panorama_peptide_url
      , analyte_peptide.panorama_protein_url
      , analyte_peptide.peptide_standard_label_type
      , analyte_peptide.cptc_catalog_id as cptc_catalog_id
      , assay_parameters_new.instrument
      , assay_parameters_new.internal_standard     
      , peptide_standard_purity_types.type as peptide_standard_purity
      , peptide_standard_purity_types.peptide_standard_purity_types_id
      , assay_parameters_new.lc
      , assay_parameters_new.column_packing
      , assay_parameters_new.column_dimensions
      , assay_parameters_new.flow_rate
      , assay_parameters_new.matrix
      , assay_parameters_new.protein_species_label as species
      , assay_parameters_new.celllysate_path
      , assay_types.label as assay_type
      , group.group_id as laboratories_id
      , group.name as laboratory_name
      , group.abbreviation as laboratory_abbreviation
      , group.primary_contact_name
      , group.primary_contact_email_address
      , group.disclaimer
      , publications.publication_citation
      , publications.publication_url
      , import_log.import_log_id
      FROM analyte_peptide
      LEFT JOIN protein on analyte_peptide.protein_id = protein.protein_id 
      LEFT JOIN import_log ON import_log.import_log_id = protein.import_log_id
      LEFT JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
      LEFT JOIN peptide_standard_purity_types ON peptide_standard_purity_types.peptide_standard_purity_types_id = assay_parameters_new.peptide_standard_purity_types_id
      LEFT JOIN `group` ON import_log.laboratory_id = group.group_id
      LEFT JOIN assay_types on assay_types.assay_types_id = assay_parameters_new.assay_types_id
      LEFT JOIN publications ON publications.import_log_id = import_log.import_log_id
      WHERE protein.gene_symbol = 'LARP1B'
      
 select * from analyte_peptide where protein_id = 1030
 


