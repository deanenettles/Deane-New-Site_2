#!/usr/bin/env node
/**
 * Gallery Build Script
 * Scans the images folder and generates a JSON manifest for the dynamic gallery.
 * Reads image captions from metadata (IPTC/EXIF/XMP description fields).
 * 
 * Run this script whenever you add or remove images:
 *   node build-gallery.js
 */

const fs = require('fs');
const path = require('path');
const ExifReader = require('exifreader');

const IMAGES_DIR = './images';
const OUTPUT_FILE = './images-manifest.json';

// Supported image extensions
const IMAGE_EXTENSIONS = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
// Note: SVG doesn't have EXIF metadata, handled separately

// Human-readable category names
const CATEGORY_NAMES = {
  'ads': 'Advertising',
  'design': 'Design',
  'fonts': 'Fonts',
  'illust': 'Illustration',
  'logos': 'Logo Design',
  'pubs': 'Publications'

};

/**
 * Extract description/caption from image metadata
 * Checks multiple metadata standards in order of preference
 */
async function getImageDescription(filePath) {
  try {
    const buffer = fs.readFileSync(filePath);
    const tags = ExifReader.load(buffer, { expanded: true });
    
    // Try different metadata fields in order of preference:
    // 1. IPTC Caption/Abstract (most common for professional images)
    // 2. IPTC Headline
    // 3. XMP Description
    // 4. XMP Title
    // 5. EXIF ImageDescription
    // 6. EXIF UserComment
    
    let description = null;
    
    // IPTC fields
    if (tags.iptc) {
      if (tags.iptc['Caption/Abstract']?.description) {
        description = tags.iptc['Caption/Abstract'].description;
      } else if (tags.iptc['Headline']?.description) {
        description = tags.iptc['Headline'].description;
      } else if (tags.iptc['Object Name']?.description) {
        description = tags.iptc['Object Name'].description;
      }
    }
    
    // XMP fields (fallback)
    if (!description && tags.xmp) {
      if (tags.xmp['Description']?.description) {
        description = tags.xmp['Description'].description;
      } else if (tags.xmp['Title']?.description) {
        description = tags.xmp['Title'].description;
      }
    }
    
    // EXIF fields (fallback)
    if (!description && tags.exif) {
      if (tags.exif['ImageDescription']?.description) {
        description = tags.exif['ImageDescription'].description;
      } else if (tags.exif['UserComment']?.description) {
        description = tags.exif['UserComment'].description;
      }
    }
    
    // Clean up the description
    if (description) {
      description = description.trim();
      // Remove null characters that sometimes appear in metadata
      description = description.replace(/\0/g, '');
    }
    
    return description || null;
    
  } catch (error) {
    // Silently fail for files that can't be read (corrupted, unsupported format, etc.)
    return null;
  }
}

/**
 * Generate a readable title from filename (fallback when no metadata)
 */
function generateTitleFromFilename(filename) {
  // Remove extension
  let title = filename.replace(/\.[^.]+$/, '');
  
  // Remove common suffixes like x800, x1200, v2, etc.
  title = title.replace(/x\d+$/i, '');
  title = title.replace(/_v\d+$/i, '');
  title = title.replace(/v\d+$/i, '');
  
  // Replace underscores and hyphens with spaces
  title = title.replace(/[_-]/g, ' ');
  
  // Clean up multiple spaces
  title = title.replace(/\s+/g, ' ').trim();
  
  // Capitalize first letter of each word
  title = title.replace(/\b\w/g, c => c.toUpperCase());
  
  return title;
}

async function scanDirectory(dir, category = null) {
  const images = [];
  
  if (!fs.existsSync(dir)) {
    console.error(`Directory not found: ${dir}`);
    return images;
  }

  const items = fs.readdirSync(dir);

  for (const item of items) {
    const fullPath = path.join(dir, item);
    const stat = fs.statSync(fullPath);

    if (stat.isDirectory()) {
      // Recurse into subdirectory, using folder name as category
      const subImages = await scanDirectory(fullPath, item);
      images.push(...subImages);
    } else if (stat.isFile()) {
      const ext = path.extname(item).toLowerCase();
      
      if (IMAGE_EXTENSIONS.includes(ext)) {
        // Try to get description from metadata
        const metadataDescription = await getImageDescription(fullPath);
        
        // Use metadata description or fall back to filename-based title
        const title = metadataDescription || generateTitleFromFilename(item);
        const hasMetadata = !!metadataDescription;
        
        images.push({
          src: fullPath.replace(/\\/g, '/'), // Normalize path separators
          filename: item,
          title: title,
          titleSource: hasMetadata ? 'metadata' : 'filename',
          category: category,
          categoryName: CATEGORY_NAMES[category] || category || 'Uncategorized',
          extension: ext
        });
        
      } else if (ext === '.svg') {
        // SVG files don't have EXIF metadata, use filename
        const title = generateTitleFromFilename(item);
        
        images.push({
          src: fullPath.replace(/\\/g, '/'),
          filename: item,
          title: title,
          titleSource: 'filename',
          category: category,
          categoryName: CATEGORY_NAMES[category] || category || 'Uncategorized',
          extension: ext
        });
      }
    }
  }

  return images;
}

async function main() {
  console.log('🖼️  Scanning images folder and reading metadata...\n');
  
  const images = await scanDirectory(IMAGES_DIR);
  
  // Sort by category, then by filename
  images.sort((a, b) => {
    if (a.category !== b.category) {
      return (a.category || '').localeCompare(b.category || '');
    }
    return a.filename.localeCompare(b.filename);
  });

  // Get unique categories
  const categories = [...new Set(images.map(img => img.category).filter(Boolean))];

  // Count metadata vs filename titles
  const metadataCount = images.filter(img => img.titleSource === 'metadata').length;
  const filenameCount = images.filter(img => img.titleSource === 'filename').length;

  const manifest = {
    generated: new Date().toISOString(),
    totalImages: images.length,
    titlesFromMetadata: metadataCount,
    titlesFromFilename: filenameCount,
    categories: categories.map(cat => ({
      id: cat,
      name: CATEGORY_NAMES[cat] || cat
    })),
    images: images
  };

  fs.writeFileSync(OUTPUT_FILE, JSON.stringify(manifest, null, 2));

  console.log(`✅ Found ${images.length} images in ${categories.length} categories:`);
  categories.forEach(cat => {
    const count = images.filter(img => img.category === cat).length;
    console.log(`   • ${CATEGORY_NAMES[cat] || cat}: ${count} images`);
  });
  
  const uncategorized = images.filter(img => !img.category).length;
  if (uncategorized > 0) {
    console.log(`   • Uncategorized: ${uncategorized} images`);
  }
  
  console.log(`\n📝 Title sources:`);
  console.log(`   • From metadata: ${metadataCount} images`);
  console.log(`   • From filename: ${filenameCount} images`);
  
  // Show which images have metadata descriptions
  if (metadataCount > 0) {
    console.log(`\n📋 Images with metadata descriptions:`);
    images.filter(img => img.titleSource === 'metadata').forEach(img => {
      console.log(`   • ${img.filename}: "${img.title}"`);
    });
  }
  
  console.log(`\n📄 Manifest saved to: ${OUTPUT_FILE}`);
  console.log('\n💡 To add descriptions, edit image metadata in Photoshop, Bridge, or Lightroom');
  console.log('   (File Info → Description/Caption field)');
}

main().catch(console.error);
