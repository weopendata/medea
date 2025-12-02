# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

MEDEA is a collaborative platform for find experts, researchers, and detectorists to work together on historical finds. The application uses a hybrid database architecture with Neo4j as the primary graph database and MariaDB/MySQL for non-graph data.

## Technology Stack

- **Backend**: PHP 7.2, Laravel 6.x
- **Primary Database**: Neo4j 2.2.x (graph database for finds and relationships)
- **Secondary Database**: MariaDB 10.10/MySQL (for non-graph data)
- **Frontend**: Vue.js 2.x, Semantic UI CSS, Vue2 Google Maps
- **Build Tool**: Laravel Mix (webpack wrapper)

## Essential Commands

### Frontend Build Commands
```bash
npm install                  # Install dependencies
npm run dev                  # Development build
npm run watch                # Watch mode for development
npm run production           # Production build (optimized)
npm run prod                 # Alias for production
```

### Backend Commands
```bash
# Install PHP dependencies
composer install

# Database migrations (MySQL only - Neo4j is managed separately)
php artisan migrate --database='mysql'

# Data management utility
php artisan medea:management   # Interactive menu for managing finds/users

# Console
php artisan tinker
```

### Laravel Standard Commands
```bash
php artisan serve              # Start development server
php artisan route:list         # List all routes
php artisan clear-compiled     # Clear compiled files
php artisan cache:clear        # Clear application cache
```

## Architecture Overview

### Dual Database Strategy

The application uses two databases for different purposes:

1. **Neo4j (Primary)**: Stores the main graph structure using CIDOC-CRM ontology
   - Historical finds and their relationships
   - Users (Person nodes)
   - Classifications, Publications, Collections
   - All graph relationships between entities
   - Configured via `NEO4J_HOST`, `NEO4J_PORT`, `NEO4J_USER`, `NEO4J_PASSWORD` environment variables

2. **MySQL (Secondary)**: Stores auxiliary data
   - Notifications (Eloquent model)
   - Other non-graph vocabulary data
   - Default Laravel migrations

### Multi-Tenancy System

The codebase implements multi-tenancy using labels on Neo4j nodes:
- All nodes have a `MEDEA_TENANT_LABEL` property (defined in `app/NodeConstants.php`)
- Tenant label is set via `DB_TENANCY_LABEL` environment variable
- `NodeService::getTenantWhereStatement()` automatically adds tenant filtering to Cypher queries
- All node creation/retrieval operations enforce tenant isolation

### Graph Data Model (CIDOC-CRM Based)

The application follows CIDOC-CRM ontology patterns:

**Base Model System** (`app/Models/Base.php`):
- All graph models extend the `Base` class
- Each model has:
  - `$NODE_NAME`: Human-readable label (e.g., "FindEvent")
  - `$NODE_TYPE`: CIDOC-CRM type (e.g., "E7")
  - `$properties`: Array of node properties
  - `$relatedModels`: Configuration for explicit relationships with cascade rules
  - `$implicitModels`: Configuration for value nodes and internal structures
  - `$uniqueIdentifier`: Typically "MEDEA_UUID" for tracking

**Key Model Lifecycle**:
1. Constructor creates node with labels and properties
2. Related models are created recursively based on configuration
3. Implicit models (value nodes) are created for simple data
4. Update operations handle cascading changes and deletions
5. Delete operations cascade according to relationship configuration

**Node Structure**:
- Every node gets CIDOC label, human label, and MEDEA_NODE label
- Unique ID nodes are created with P1 relationship
- Timestamps (created_at, updated_at) in ISO8601 format
- Value nodes are labeled with parent's UUID for cleanup

### Repository Pattern

**BaseRepository** (`app/Repositories/BaseRepository.php`):
- Each model type has a corresponding repository
- Repositories handle filtering by label and tenant
- Common methods: `getById()`, `delete()`, `expandValues()`
- Specialized repositories extend base (e.g., `FindRepository`, `UserRepository`)

### Frontend Architecture

**Vue.js 2.x Structure**:
- Components auto-registered from `resources/assets/js/components/`
- Main entry point: `resources/assets/js/main.js`
- Uses VueResource for HTTP requests (not Axios in components)
- Semantic UI for styling (not Bootstrap in UI)
- Google Maps integration via vue2-google-maps

**Key Components**:
- `FindsOverview.vue`: Main finds listing and filtering
- `FindEventDetail.vue`: Individual find details
- `CreateFind.vue`: Find creation wizard
- `UserDetail.vue`: User profile display
- `Classification.vue`: Classification voting system

**Global Configuration**:
- CSRF token set in `window.csrf`
- API calls automatically include CSRF token via `Vue.http.headers`
- jQuery and Lodash available globally

### Routes Structure

Located in `app/Http/routes.php`:
- Public routes: home, about, contact, finds browsing
- Authenticated routes under `auth` middleware
- Role-based routes using custom `roles` middleware:
  - `administrator`: User management, exports
  - `validator|detectorist`: Object validation
  - `detectorist|registrator|vondstexpert`: Classification management
- API routes prefixed with `api/`
- RESTful resource controllers for main entities

## Neo4j Full-Text Search

The application uses Neo4j's legacy full-text indexing with Lucene:
- Auto-index enabled on `fulltext_description` property
- Configured with `to_lower_case: true` analyzer
- To rebuild index: Set `fulltext_description=fulltext_description` on all nodes
- Configuration in `neo4j.properties`: `node_auto_indexing=true`, `node_keys_indexable=fulltext_description`

## Development Workflow

### Working with Finds
Finds are represented as `FindEvent` (E7 nodes) with complex relationships:
- FindSpot (E53): Location information
- ProductionEvent (E12): Creation/production details
- Classifications: Expert classifications with voting
- Collections: Organizational groupings
- Photographs: Image attachments

### Working with Models
When modifying or creating models:
1. Define `$NODE_NAME` and `$NODE_TYPE` static properties
2. Configure `$properties` array with property definitions
3. Set up `$relatedModels` with relationship configurations including:
   - `key`: Input key name
   - `model_name`: Related model class name
   - `required`: Whether relationship is required
   - `cascade_delete`: Whether to delete related nodes
   - `link_only`: Whether to link existing nodes vs creating new
   - `plural`: Whether multiple instances allowed
4. Define `$implicitModels` for value nodes with CIDOC types
5. Implement custom create methods for complex implicit structures

### Working with Repositories
- Always use repositories instead of direct model access from controllers
- Repositories handle tenant filtering automatically
- Use `expandValues()` to get full node data with relationships
- Cypher queries should include `NodeService::getTenantWhereStatement()`

### File Upload Handling
- Uploads stored in `public/uploads/` directory (create if missing)
- Images processed via Intervention Image library
- Configure storage settings in `config/filesystems.php`

## Environment Configuration

Key environment variables:
- `NEO4J_HOST`, `NEO4J_PORT`, `NEO4J_USER`, `NEO4J_PASSWORD`: Neo4j connection
- `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: MySQL connection
- `DB_TENANCY_LABEL`: Multi-tenancy label (required for all operations)
- Google Maps API key is currently hardcoded in `main.js` (should be moved to env)

## Testing and Debugging

- PHPUnit configured for testing: `vendor/bin/phpunit`
- Laravel's `dd()` and `dump()` available for debugging
- Use `php artisan medea:management` for data cleanup/management
- Check both Neo4j browser (usually port 7474) and MySQL for data verification
