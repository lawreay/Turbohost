#!/usr/bin/env python3
"""
Batch convert all markdown files to PDF
"""

import os
import sys
import re
from pathlib import Path

try:
    from reportlab.lib.pagesizes import A4
    from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
    from reportlab.lib.units import mm
    from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, PageBreak
    from reportlab.lib import colors
    from reportlab.lib.enums import TA_CENTER, TA_JUSTIFY
except ImportError:
    print("Installing required packages...")
    os.system(f'{sys.executable} -m pip install reportlab -q')
    from reportlab.lib.pagesizes import A4
    from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
    from reportlab.lib.units import mm
    from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, PageBreak
    from reportlab.lib import colors
    from reportlab.lib.enums import TA_CENTER, TA_JUSTIFY

def convert_md_to_pdf(md_file, pdf_file, title="Document"):
    """Convert markdown file to PDF"""
    
    if not os.path.exists(md_file):
        print(f"  ✗ File not found: {md_file}")
        return False
    
    print(f"  Converting: {os.path.basename(md_file)}...")
    
    with open(md_file, 'r', encoding='utf-8') as f:
        md_content = f.read()
    
    # Create PDF
    doc = SimpleDocTemplate(
        pdf_file,
        pagesize=A4,
        rightMargin=20*mm,
        leftMargin=20*mm,
        topMargin=20*mm,
        bottomMargin=20*mm,
    )
    
    # Create styles
    styles = getSampleStyleSheet()
    
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
    
    # Add title if provided
    if title:
        story.append(Paragraph(title, title_style))
        story.append(Spacer(1, 12))
    
    # Process content
    lines = md_content.split('\n')
    i = 0
    while i < len(lines):
        line = lines[i]
        
        if not line.strip():
            i += 1
            continue
        
        # Headers
        if line.startswith('# ') and not line.startswith('## '):
            text = line[2:].strip()
            story.append(Spacer(1, 12))
            story.append(Paragraph(text, heading1_style))
            story.append(Spacer(1, 6))
        
        elif line.startswith('## ') and not line.startswith('### '):
            text = line[3:].strip()
            story.append(Spacer(1, 10))
            story.append(Paragraph(text, heading2_style))
            story.append(Spacer(1, 4))
        
        elif line.startswith('### '):
            text = line[4:].strip()
            story.append(Paragraph(text, heading3_style))
            story.append(Spacer(1, 3))
        
        elif line.strip().startswith('- ') or line.strip().startswith('✓ ') or line.strip().startswith('⚠️ '):
            text = line.strip()[2:].strip()
            story.append(Paragraph(f"• {text}", bullet_style))
        
        elif line.strip() == '---':
            story.append(Spacer(1, 12))
        
        elif line.strip() and not line.startswith('|'):
            text = line.strip()
            # Escape HTML special chars first to prevent parsing errors
            text = text.replace('&', '&amp;').replace('<', '&lt;').replace('>', '&gt;')
            # Then apply markdown formatting
            text = re.sub(r'\*\*(.*?)\*\*', r'<b>\1</b>', text)
            text = re.sub(r'\*(.*?)\*', r'<i>\1</i>', text)
            text = re.sub(r'`(.*?)`', r'<font face="Courier">\1</font>', text)
            text = re.sub(r'\[(.*?)\]\((.*?)\)', r'\1', text)
            story.append(Paragraph(text, body_style))
        
        i += 1
    
    # Build PDF
    try:
        doc.build(story)
        size = os.path.getsize(pdf_file) / 1024
        print(f"  ✓ Created: {os.path.basename(pdf_file)} ({size:.1f} KB)")
        return True
    except Exception as e:
        print(f"  ✗ Error: {e}")
        return False

def main():
    workspace_dir = r'c:\wamp64\www\TurboHostMw'
    
    # Files to convert
    conversions = [
        {
            'md': os.path.join(workspace_dir, 'TurboHostMw_User_Guide.md'),
            'pdf': os.path.join(workspace_dir, 'TurboHostMw_User_Guide.pdf'),
            'title': 'TurboHostMw User Guide'
        },
        {
            'md': os.path.join(workspace_dir, 'TurboHostMw_Technical_Documentation.md'),
            'pdf': os.path.join(workspace_dir, 'TurboHostMw_Technical_Documentation.pdf'),
            'title': 'TurboHostMw Technical Documentation'
        },
        {
            'md': os.path.join(workspace_dir, 'TurboHostMw_Quick_Start_Guide.md'),
            'pdf': os.path.join(workspace_dir, 'TurboHostMw_Quick_Start_Guide.pdf'),
            'title': 'TurboHostMw Quick Start Guide'
        }
    ]
    
    print("\n" + "="*60)
    print("TurboHostMw Documentation PDF Conversion")
    print("="*60 + "\n")
    
    success_count = 0
    for conversion in conversions:
        if convert_md_to_pdf(
            conversion['md'],
            conversion['pdf'],
            conversion['title']
        ):
            success_count += 1
    
    print("\n" + "="*60)
    print(f"Conversion Complete: {success_count}/{len(conversions)} files")
    print("="*60 + "\n")
    
    # List created files
    print("Created Documentation Files:\n")
    for conversion in conversions:
        pdf_path = conversion['pdf']
        if os.path.exists(pdf_path):
            size = os.path.getsize(pdf_path) / 1024
            print(f"  📄 {os.path.basename(pdf_path)}")
            print(f"     Size: {size:.1f} KB")
            print(f"     Location: {pdf_path}\n")

if __name__ == '__main__':
    main()
