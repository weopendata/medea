# MEDEA Export Commands Documentation

This document explains how to use the two export commands created for the MEDEA project end-of-life data extraction.

## Commands Overview

Two Artisan commands have been created:
1. `medea:export-finds-csv` - Exports all find basic data to CSV
2. `medea:export-images` - Exports all find images to a ZIP file with a mapping CSV

## Command 1: Export Finds to CSV

### Description
Exports all find basic data (as shown on the detail page) to a CSV file.

### Usage
```bash
php artisan medea:export-finds-csv
```

### Optional Parameters
```bash
php artisan medea:export-finds-csv --output=/path/to/custom/output.csv
```

If no output path is specified, the file will be saved to: `storage/app/finds_export.csv`

### CSV Columns
The exported CSV includes the following columns:
- MEDEA ID (the MEDEA_UUID)
- Internal ID (the Neo4j node ID)
- Find Date
- Location (Locality)
- Latitude
- Longitude
- Coordinate Accuracy
- Find Spot Type
- Find Spot Title
- Finder Name (respects privacy settings)
- Finder Email
- Finder Detectorist Number
- Object Category
- Object Material
- Object Period
- Object Description
- Production Technique
- Modification Technique
- Inscription Note
- Dimensions (formatted as "type: value unit; type: value unit")
- Collection Title
- Object Number
- Validation Status
- Validated By
- Validated At
- Created At
- Updated At
- Classification Count

### Example
```bash
php artisan medea:export-finds-csv --output=/tmp/medea_finds.csv
```

## Command 2: Export Find Images

### Description
Exports all find images to a ZIP file with proper naming convention and creates a mapping CSV file.

### Image Naming Convention
Images are renamed according to the pattern: `{MEDEA_ID}_{image_number}.{extension}`

Examples:
- First image: `MEDEA12345abc_1.jpg`
- Second image: `MEDEA12345abc_2.jpg`
- Third image: `MEDEA12345abc_3.png`

### Usage
```bash
php artisan medea:export-images
```

### Optional Parameters
```bash
php artisan medea:export-images --output=/path/to/custom/directory
```

If no output path is specified, files will be saved to: `storage/app/image_export/`

### Output Files
The command creates two files in the output directory:

1. **medea_images.zip** - Contains all find images with renamed filenames
2. **image_mapping.csv** - Mapping table with the following columns:
   - MEDEA ID (the MEDEA_UUID)
   - Vondst ID (the find identifier shown on detail page as ID-{identifier})
   - Internal Find ID (Neo4j node ID)
   - Image Number
   - Filename (new filename in ZIP)
   - Original Path
   - Width
   - Height

### Example
```bash
php artisan medea:export-images --output=/tmp/medea_export
```

This will create:
- `/tmp/medea_export/medea_images.zip`
- `/tmp/medea_export/image_mapping.csv`

## Important Notes

1. **All Finds**: Both commands export ALL finds in the database (no pagination limits applied).

2. **Permissions**: Ensure the PHP process has write permissions to the output directories.

3. **Disk Space**: The image export command creates a temporary directory during processing. Ensure you have sufficient disk space (at least 2x the total size of all images).

4. **Memory**: For large datasets, you may need to increase PHP's memory limit:
   ```bash
   php -d memory_limit=512M artisan medea:export-finds-csv
   php -d memory_limit=1024M artisan medea:export-images
   ```

5. **Progress Bars**: Both commands show progress bars during execution, so you can monitor the export process.

6. **Error Handling**: Both commands will continue processing even if individual records fail, and will report the number of errors at the end.

7. **Multi-Tenancy**: The commands respect the `DB_TENANCY_LABEL` environment variable and only export data for the configured tenant.

## Troubleshooting

### Command not found
If the commands are not recognized, try:
```bash
php artisan clear-compiled
php artisan cache:clear
composer dump-autoload
```

### Missing images
If images are reported as "not found", ensure:
- The `public/uploads/` directory exists
- Image paths in the database are correct
- The web server user has read permissions on image files

### CSV encoding issues
The CSV files are created using UTF-8 encoding. If you experience encoding issues when opening in Excel, try:
- Import the CSV using Excel's "Data > From Text/CSV" feature
- Use a text editor that supports UTF-8 (VS Code, Sublime Text, etc.)
- Use LibreOffice Calc instead of Excel

## File Locations

Default output locations:
- Find CSV: `storage/app/finds_export.csv`
- Image ZIP: `storage/app/image_export/medea_images.zip`
- Image mapping CSV: `storage/app/image_export/image_mapping.csv`

## Running in Production

When running on production server:
1. SSH into the production server
2. Navigate to the application directory
3. Run the commands with appropriate paths:
   ```bash
   php artisan medea:export-finds-csv --output=/var/www/exports/finds_export.csv
   php artisan medea:export-images --output=/var/www/exports/image_export
   ```
4. Download the files using SCP or SFTP:
   ```bash
   scp user@server:/var/www/exports/finds_export.csv ./
   scp user@server:/var/www/exports/image_export/medea_images.zip ./
   scp user@server:/var/www/exports/image_export/image_mapping.csv ./
   ```
