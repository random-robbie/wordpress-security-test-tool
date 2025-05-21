# WordPress Security Test Tool

![GitHub](https://img.shields.io/github/license/random-robbie/wordpress-security-test-tool)

A WordPress plugin designed specifically for security professionals to demonstrate command execution vulnerabilities in a controlled testing environment.

## ⚠️ Security Warning

**This plugin is for SECURITY TESTING PURPOSES ONLY**

This tool:
- Provides authenticated remote command execution
- Should NEVER be installed on production websites
- Must only be used with proper authorization and in controlled environments
- Is intended for educational and security demonstration purposes

## Features

- Password-protected interface for command execution
- Environment restrictions (only functions on testing domains)
- Command allowlisting for controlled testing
- Simple web interface for testing command injection vulnerabilities
- WordPress admin integration

## Installation

1. Clone this repository to your WordPress plugins directory:
   ```
   git clone https://github.com/random-robbie/wordpress-security-test-tool.git wp-content/plugins/security-test-tool
   ```

2. Activate the plugin from the WordPress dashboard

3. Access the tool at `your-site.com/security-test`

## Usage

1. Navigate to `your-site.com/security-test`
2. Enter the hardcoded password to access the command interface
3. Execute allowed commands to demonstrate command injection vulnerabilities
4. Use the output to educate clients on security risks and proper remediation techniques

## Configuration

The plugin includes the following configurable elements in the source code:

- `$access_password`: The hardcoded password that protects the interface
- `$allowed_commands`: Array of commands that are permitted to be executed
- Environment checks: By default, only works on `localhost` and `kubernetes.docker.internal`

## Security Considerations

This plugin implements the following security measures to prevent misuse:

1. Environment restriction: Only functions on defined testing domains
2. Password protection: Requires authentication to access functionality
3. Command allowlisting: Restricts command execution to a predefined set
4. Clear warning messages: Indicates this is for testing only

## Legal Disclaimer

This tool is provided for authorized security testing ONLY. The author assumes no liability for misuse or damage caused by this software. Users are responsible for obtaining proper authorization before conducting security tests.

## Contributing

Contributions to improve the security and functionality of this tool are welcome:

1. Fork the repository
2. Create a feature branch: `git checkout -b new-feature`
3. Commit your changes: `git commit -am 'Add new security feature'`
4. Push to the branch: `git push origin new-feature`
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Author

Created by [random-robbie](https://github.com/random-robbie)
