# MCP Server Setup for OpenAPI Schema Exploration

This guide will help you set up the MCP (Model Context Protocol) server for exploring your OpenAPI schema (`my-schema.yaml`).

## Prerequisites

1. **Install Node.js**: 
   - Download and install Node.js from https://nodejs.org/
   - Verify installation:
     ```bash
     node --version
     npm --version
     ```

## Installation Steps

### Option 1: Using npx (Recommended - No Installation Required)

The MCP server can be run directly using `npx` without installing it globally:

```bash
npx -y mcp-openapi-schema D:\adminjadid\my-schema.yaml
```

### Option 2: Install Globally

If you prefer to install it globally:

```bash
npm install -g mcp-openapi-schema
```

Then run:
```bash
mcp-openapi-schema D:\adminjadid\my-schema.yaml
```

## Cursor IDE Configuration

### Automatic Configuration

The MCP server configuration file has been created at `.cursor/mcp-config.json`. However, Cursor may require manual configuration.

### Manual Configuration for Cursor

1. **Locate Cursor Settings**:
   - On Windows: `%APPDATA%\Cursor\User\settings.json` or check Cursor's MCP settings
   - The exact location may vary based on your Cursor version

2. **Add MCP Server Configuration**:
   
   Open your Cursor settings file and add the following configuration:

   ```json
   {
     "mcpServers": {
       "OpenAPI Schema": {
         "command": "npx",
         "args": [
           "-y",
           "mcp-openapi-schema",
           "D:\\adminjadid\\my-schema.yaml"
         ]
       }
     }
   }
   ```

   **Important**: Replace `D:\\adminjadid\\my-schema.yaml` with the absolute path to your `my-schema.yaml` file.

3. **Restart Cursor**: After updating the configuration, restart Cursor IDE to apply the changes.

## Verifying the Setup

Once configured, you should be able to:
- Explore API paths and operations
- View detailed endpoint information
- Inspect request and response schemas
- Search across the API specification

## Customizing the Schema

Edit `my-schema.yaml` to match your actual API structure. The current schema includes examples based on your API endpoints:
- `/api/auth.php` - Authentication endpoints
- `/api/posts.php` - Post management
- `/api/projects.php` - Project management

## Troubleshooting

1. **Node.js not found**: Ensure Node.js is installed and added to your system PATH
2. **Schema file not found**: Verify the path to `my-schema.yaml` is correct
3. **MCP server not working**: Check Cursor's console/logs for error messages
4. **Path issues on Windows**: Use double backslashes (`\\`) or forward slashes (`/`) in paths

## Resources

- MCP OpenAPI Schema Server: https://github.com/hannesj/mcp-openapi-schema
- OpenAPI Specification: https://swagger.io/specification/

