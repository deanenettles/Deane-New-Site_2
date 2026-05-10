# RatioApp - InDesign Type Scale Plugin

A CEP (Common Extensibility Platform) plugin for Adobe InDesign that generates typographic scales and creates paragraph styles based on mathematical ratios.

## Features

- **Typographic Scale Calculator**: Generate font sizes using various musical and mathematical ratios
- **Preset Ratios**: Minor Second, Major Second, Minor Third, Major Third, Perfect Fourth, Augmented Fourth, Perfect Fifth, and Golden Ratio
- **Custom Ratios**: Enter any custom ratio value
- **Adjustable Steps**: Control how many size levels to generate (3-10)
- **Live Preview**: See the generated scale before creating styles
- **One-Click Style Creation**: Generate InDesign paragraph styles with proper typography settings

## Installation

### Method 1: Manual Installation (Development)

1. **Find your extensions folder:**
   - **macOS**: `~/Library/Application Support/Adobe/CEP/extensions/`
   - **Windows**: `C:\Users\[USERNAME]\AppData\Roaming\Adobe\CEP\extensions\`

2. **Copy the plugin folder:**
   ```bash
   cp -r indesign-ratio-plugin ~/Library/Application\ Support/Adobe/CEP/extensions/com.deane.ratioapp
   ```

3. **Enable unsigned extensions (for development):**
   - **macOS**: Open Terminal and run:
     ```bash
     defaults write com.adobe.CSXS.11 PlayerDebugMode 1
     ```
   - **Windows**: Edit registry key `HKEY_CURRENT_USER\Software\Adobe\CSXS.11` and add string value `PlayerDebugMode` with data `1`

4. **Restart InDesign**

5. **Open the panel:** Go to `Window > Extensions > RatioApp Type Scale`

### Method 2: ZXP Package (Production)

Package the extension as a .zxp file using ZXPSignCmd and distribute via Adobe Exchange or directly.

## Usage

1. **Set Base Size**: Enter your base font size in points (default: 16pt)

2. **Choose a Ratio**: Select from preset ratios or choose "Custom" to enter your own
   - **Minor Second (1.067)**: Very subtle progression
   - **Major Second (1.125)**: Gentle progression
   - **Minor Third (1.200)**: Moderate progression
   - **Major Third (1.250)**: Classic progression (default)
   - **Perfect Fourth (1.333)**: Strong progression
   - **Augmented Fourth (1.414)**: √2, used in traditional typography
   - **Perfect Fifth (1.500)**: Bold progression
   - **Golden Ratio (1.618)**: Natural, harmonious progression

3. **Adjust Scale Steps**: Use the slider to set how many heading levels to generate (3-10)

4. **Set Style Prefix**: Enter a prefix for your style names (e.g., "Heading", "Body", "Display")

5. **Preview**: The scale preview updates in real-time as you adjust settings

6. **Create Styles**: Click "Create Paragraph Styles" to generate the styles in InDesign

## Generated Styles

The plugin creates a style group called "RatioApp Scale" containing:
- Paragraph styles with appropriate font sizes
- Leading set at 140% of font size
- Negative tracking for larger sizes (better readability)
- Bold/Semibold weights for top heading levels

## Requirements

- Adobe InDesign CC 2020 or later
- CEP 9.0 or later

## Troubleshooting

**Panel doesn't appear in Extensions menu:**
- Ensure the extension is in the correct folder
- Verify PlayerDebugMode is enabled
- Restart InDesign completely

**Styles not being created:**
- Make sure a document is open in InDesign
- Check the JavaScript console (enable via `.debug` file) for errors

## License

MIT License - Free for personal and commercial use.
