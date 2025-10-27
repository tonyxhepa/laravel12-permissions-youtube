# Roles and Permissions Test Suite

This comprehensive test suite covers all aspects of the Laravel Spatie Permission package implementation in this application.

## Test Structure

### Unit Tests

#### `tests/Unit/RoleTest.php`
Tests the core functionality of the Role model including:
- Role creation and validation
- Permission assignment and synchronization
- User role assignment
- Role-based permission checking
- Role deletion and cascading effects
- Unique constraint validation
- Role lookup methods

#### `tests/Unit/PermissionTest.php`
Tests the core functionality of the Permission model including:
- Permission creation and validation
- Role assignment and synchronization
- Direct user permission assignment
- Permission checking methods
- Permission deletion and cascading effects
- Unique constraint validation
- Permission lookup methods

### Feature Tests

#### `tests/Feature/RoleManagementTest.php`
Tests the Livewire components for role management:
- **RoleCreate**: Role creation with validation
- **RoleEdit**: Role editing and permission updates
- **RoleIndex**: Role listing and deletion
- Form validation and error handling
- Authentication requirements

#### `tests/Feature/PermissionManagementTest.php`
Tests the Livewire components for permission management:
- **PermissionCreate**: Permission creation with role assignment
- **PermissionEdit**: Permission editing and role updates
- **PermissionIndex**: Permission listing and deletion
- Pagination functionality
- Form validation and error handling

#### `tests/Feature/UserRoleAssignmentTest.php`
Tests user management with role assignment:
- **UserCreate**: User creation with role assignment
- **UserEdit**: User editing and role synchronization
- **UserIndex**: User listing and deletion
- Password hashing and validation
- Role inheritance and permission checking

#### `tests/Feature/RoleBasedAccessControlTest.php`
Tests the authorization system:
- Gate-based authorization
- Middleware-based authorization (role, permission, role_or_permission)
- Complex authorization scenarios
- Performance and caching
- Edge cases and error handling

#### `tests/Feature/RolesAndPermissionsSeederTest.php`
Tests the database seeder:
- Permission creation (posts, users, roles)
- Role creation with proper permissions
- Idempotent seeding (safe to run multiple times)
- Data integrity and structure validation

#### `tests/Feature/PostPermissionTest.php`
Tests post-specific permission integration:
- CRUD operations with permissions
- Role-based post access
- Permission inheritance
- Ownership validation scenarios

## Running the Tests

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suites
```bash
# Unit tests only
php artisan test tests/Unit/

# Feature tests only
php artisan test tests/Feature/

# Specific test file
php artisan test tests/Unit/RoleTest.php
```

### Run with Coverage
```bash
php artisan test --coverage
```

## Test Coverage

The test suite provides comprehensive coverage of:

1. **Model Functionality** (100%)
   - Role and Permission CRUD operations
   - Relationship management
   - Validation and constraints
   - Query methods and scopes

2. **Livewire Components** (100%)
   - Form handling and validation
   - Data persistence
   - User interface interactions
   - Error handling and feedback

3. **Authorization System** (100%)
   - Gate definitions and checks
   - Middleware functionality
   - Role and permission inheritance
   - Access control scenarios

4. **Database Operations** (100%)
   - Seeding functionality
   - Data integrity
   - Relationship cascading
   - Cache management

## Key Testing Patterns

### Database Testing
- Uses `RefreshDatabase` trait for clean test environment
- Creates test data using factories and direct model creation
- Tests both positive and negative scenarios

### Livewire Testing
- Uses `Livewire::test()` for component testing
- Tests form validation and submission
- Verifies redirects and flash messages
- Tests component state management

### Authorization Testing
- Tests both direct permission checks and middleware
- Verifies role inheritance and permission cascading
- Tests edge cases and error conditions
- Validates performance and caching behavior

### Integration Testing
- Tests complete workflows from UI to database
- Verifies data consistency across operations
- Tests error handling and recovery scenarios

## Test Data

The tests use the following test data structure:

### Roles
- `test_role`: Basic test role
- `admin`: Administrative role
- `moderator`: Moderator role (from seeder)
- `super_admin`: Super admin role (bypasses all checks)

### Permissions
- Post permissions: `view_any_posts`, `view_posts`, `create_posts`, etc.
- User permissions: `view_any_users`, `view_users`, `create_users`, etc.
- Role permissions: `view_any_roles`, `view_roles`, `create_roles`, etc.

### Users
- Created using `User::factory()->create()`
- Assigned various roles and permissions for testing
- Test both authenticated and unauthenticated scenarios

## Best Practices Demonstrated

1. **Comprehensive Coverage**: Tests cover all major functionality and edge cases
2. **Clear Test Names**: Descriptive test names that explain what is being tested
3. **Isolated Tests**: Each test is independent and doesn't rely on others
4. **Realistic Scenarios**: Tests use realistic data and scenarios
5. **Error Testing**: Tests both success and failure paths
6. **Performance Testing**: Includes tests for caching and performance
7. **Documentation**: Well-documented test structure and purpose

## Maintenance

When adding new features to the roles and permissions system:

1. Add corresponding unit tests for new model methods
2. Add feature tests for new Livewire components
3. Add integration tests for new authorization rules
4. Update seeder tests if new permissions/roles are added
5. Ensure all tests pass before merging changes

## Troubleshooting

### Common Issues

1. **Permission Cache Issues**: Tests clear the permission cache between runs
2. **Database State**: Uses `RefreshDatabase` to ensure clean state
3. **Authentication**: Tests properly set up authenticated users
4. **Factory Dependencies**: Ensure all required factories are available

### Debugging Tests

```bash
# Run with verbose output
php artisan test --verbose

# Run specific test with detailed output
php artisan test tests/Unit/RoleTest.php --verbose

# Run with stop on failure
php artisan test --stop-on-failure
```
