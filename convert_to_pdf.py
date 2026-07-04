#!/usr/bin/env python3
"""
Convert Markdown to PDF using reportlab (no system dependencies)
"""

import os
import sys
import re

try:
    from reportlab.lib.pagesizes import letter, A4
    from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
    from reportlab.lib.units import inch, mm
    from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, PageBreak, Table, TableStyle
    from reportlab.lib import colors
    from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_JUSTIFY, TA_RIGHT
except ImportError:
    print("Installing required packages...")
    os.system(f'{sys.executable} -m pip install reportlab -q')
    try:
        from reportlab.lib.pagesizes import letter, A4
        from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
        from reportlab.lib.units import inch, mm
        from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, PageBreak, Table, TableStyle
        from reportlab.lib import colors
        from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_JUSTIFY, TA_RIGHT
    except ImportError as e:
        print(f"Failed to import reportlab: {e}")
        sys.exit(1)

def parse_markdown_sections(md_text):
    """Parse markdown into sections"""
    sections = []
    
    # Split by main headings
    parts = re.split(r'^(#[^#].*?)$', md_text, flags=re.MULTILINE)
    
    return parts

# Read markdown file
md_file = r'c:\wamp64\www\TurboHostMw\TurboHostMw_User_Guide.md'
pdf_file = r'c:\wamp64\www\TurboHostMw\TurboHostMw_User_Guide.pdf'

if not os.path.exists(md_file):
    print(f"Error: Markdown file not found: {md_file}")
    sys.exit(1)

print(f"Reading markdown file: {md_file}")
with open(md_file, 'r', encoding='utf-8') as f:
    md_content = f.read()

# Create PDF
print("Creating PDF document...")
doc = SimpleDocTemplate(
    pdf_file,
    pagesize=A4,
    rightMargin=20*mm,
    leftMargin=20*mm,
    topMargin=20*mm,
    bottomMargin=20*mm,
)

# Create custom styles
styles = getSampleStyleSheet()

# Define custom styles
title_style = ParagraphStyle(
    'CustomTitle',
    parent=styles['Heading1'],
    fontSize=24,
    textColor=colors.HexColor('#1a73e8'),
    spaceAfter=6,
    alignment=TA_CENTER,
    fontName='Helvetica-Bold',
)

heading1_style = ParagraphStyle(
    'CustomHeading1',
    parent=styles['Heading1'],
    fontSize=16,
    textColor=colors.HexColor('#1a73e8'),
    spaceAfter=6,
    spaceBefore=12,
    fontName='Helvetica-Bold',
)

heading2_style = ParagraphStyle(
    'CustomHeading2',
    parent=styles['Heading2'],
    fontSize=13,
    textColor=colors.HexColor('#0066cc'),
    spaceAfter=4,
    spaceBefore=10,
    fontName='Helvetica-Bold',
)

heading3_style = ParagraphStyle(
    'CustomHeading3',
    parent=styles['Heading3'],
    fontSize=11,
    textColor=colors.HexColor('#0066cc'),
    spaceAfter=3,
    spaceBefore=8,
    fontName='Helvetica-Bold',
)

body_style = ParagraphStyle(
    'CustomBody',
    parent=styles['BodyText'],
    fontSize=10,
    alignment=TA_JUSTIFY,
    spaceAfter=8,
)

bullet_style = ParagraphStyle(
    'CustomBullet',
    parent=styles['BodyText'],
    fontSize=10,
    leftIndent=20,
    spaceAfter=4,
)

# Build story
story = []

# Title
story.append(Paragraph("TurboHostMw User Guide", title_style))
story.append(Paragraph("Complete Platform Documentation for Clients", styles['Normal']))
story.append(Spacer(1, 12))

# Process markdown content
lines = md_content.split('\n')
i = 0
while i < len(lines):
    line = lines[i]
    
    # Skip empty lines
    if not line.strip():
        i += 1
        continue
    
    # H1
    if line.startswith('# ') and not line.startswith('## '):
        text = line[2:].strip()
        story.append(Spacer(1, 12))
        story.append(Paragraph(text, heading1_style))
        story.append(Spacer(1, 6))
    
    # H2
    elif line.startswith('## ') and not line.startswith('### '):
        text = line[3:].strip()
        story.append(Spacer(1, 10))
        story.append(Paragraph(text, heading2_style))
        story.append(Spacer(1, 4))
    
    # H3
    elif line.startswith('### '):
        text = line[4:].strip()
        story.append(Paragraph(text, heading3_style))
        story.append(Spacer(1, 3))
    
    # Bullet points
    elif line.strip().startswith('- ') or line.strip().startswith('✓ ') or line.strip().startswith('⚠️ '):
        text = line.strip()[2:].strip()
        story.append(Paragraph(f"• {text}", bullet_style))
    
    # Horizontal line
    elif line.strip() == '---':
        story.append(Spacer(1, 12))
    
    # Regular paragraph
    elif line.strip() and not line.startswith('|'):
        # Clean up the text
        text = line.strip()
        # Convert markdown formatting to simple text
        text = re.sub(r'\*\*(.*?)\*\*', r'<b>\1</b>', text)
        text = re.sub(r'\*(.*?)\*', r'<i>\1</i>', text)
        text = re.sub(r'`(.*?)`', r'<font face="Courier">\1</font>', text)
        text = re.sub(r'\[(.*?)\]\((.*?)\)', r'\1', text)
        
        story.append(Paragraph(text, body_style))
    
    i += 1

# Add page break before footer
story.append(PageBreak())

# Footer
story.append(Spacer(1, 12))
story.append(Paragraph("<b>TurboHostMw</b> - Your Website Hosting Made Simple", styles['Normal']))
story.append(Paragraph("Last Updated: January 2026", styles['Normal']))
story.append(Paragraph("For support: phukal@mau.adventist.org", styles['Normal']))

# Build PDF
try:
    doc.build(story)
    print(f"✓ PDF created successfully: {pdf_file}")
    if os.path.exists(pdf_file):
        file_size = os.path.getsize(pdf_file) / 1024
        print(f"  File size: {file_size:.1f} KB")
except Exception as e:
    print(f"Error creating PDF: {e}")
    import traceback
    traceback.print_exc()
    sys.exit(1)
