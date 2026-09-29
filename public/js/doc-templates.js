/**
 * doc-templates.js
 * Barangay Document Template Engine
 *
 * Usage:
 *   BisDoc.setCensus({ name, age, civil, zone, occupation });
 *   BisDoc.setCaptain('FULL NAME');
 *   BisDoc.setBarangay({ barangay_name, municipality, province, region, full_address, office_header, ... });
 *   const html = BisDoc.build('clearance', 'Juan Dela Cruz', 'Employment');
 *   document.getElementById('preview').innerHTML = html;
 *   BisDoc.print('clearance', 'Juan Dela Cruz', 'Employment');
 */

const BisDoc = (function () {

    // ── Census data (set per page load) ──────────────────────────────────────
    let _census = { name: '', age: '', civil: '', zone: '', occupation: '' };

    // ── Barangay settings (overridable from PHP) ──────────────────────────────
    let _b = {
        barangay_name : 'BARANGAY BACOLOD',
        municipality  : 'Municipality of Bato',
        province      : 'Province of Camarines Sur',
        region        : 'Region V',
        country       : 'Republic of the Philippines',
        full_address  : 'Barangay Bacolod, Bato, Camarines Sur',
        office_header : 'OFFICE OF THE PUNONG BARANGAY',
        captain_name  : 'Punong Barangay',
        captain_title : 'Punong Barangay',
    };

    // ── Per-document snapshot (set when rendering an already-approved doc) ────
    // Populated via setSnapshot() before build()/print(), then cleared after use
    // so pending documents still use the live captain name and today's date.
    let _snapshot = { captain_name: null, issued_date: null };
    let _contentOverride = null;

    function setCensus(data)   { _census = Object.assign(_census, data); }
    function setCaptain(name)  { if (name) _b.captain_name = name; }

    /**
     * Override barangay identity settings.
     * Call with the PHP-injected object on each page that uses BisDoc.
     */
    function setBarangay(data) {
        if (data) _b = Object.assign(_b, data);
    }

    function setContent(content) {
      _contentOverride = content && typeof content === 'object' ? content : null;
    }

    function escapeText(value) {
      return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function normalizeBodyText(paragraphs) {
      const items = Array.isArray(paragraphs) ? paragraphs : [];
      return items
        .map(paragraph => String(paragraph ?? '').replace(/\r\n/g, '\n').trim())
        .filter(paragraph => paragraph !== '')
        .join('\n\n');
    }

    function parseBodyText(value) {
      return String(value ?? '')
        .replace(/\r\n/g, '\n')
        .split(/\n\s*\n+/)
        .map(paragraph => paragraph.replace(/\n+/g, ' ').replace(/\s+/g, ' ').trim())
        .filter(paragraph => paragraph !== '');
    }

    function editableContent(docKey, name, civil, zone, purpose, d) {
      const zoneText = zone ? zone + ', ' : '';
      const address = zoneText + _b.full_address;
      const dateText = `${ordinal(d.day)} day of ${d.month}, ${d.year}`;
      const defaults = {
        clearance: {
          title: 'BARANGAY CLEARANCE',
          salutation: 'TO WHOM IT MAY CONCERN,',
          paragraphs: [
            `This is to certify that ${name}, a legal age, ${civil} and a bonafide resident of ${address}.`,
            'He/She possessed good moral character, trustworthy, a law-abiding Filipino Citizen and cooperative to all undertakings for the progress of the community.',
            `This Barangay Clearance is being issued upon the request of the above-named person for ${purpose} and for whatever legal purposes it may serve.`,
            `Given this ${dateText} at ${_b.full_address}, Philippines.`,
          ],
          signatureLabel: 'Approved by:',
          signatureTitle: _b.captain_title,
        },
        residency: {
          title: 'BARANGAY CERTIFICATION',
          salutation: 'TO WHOM IT MAY CONCERN:',
          paragraphs: [
            `This is to certify that ${name}, both of legal age, ${civil} Filipino and a Bonafide resident of ${address}.`,
            'This further certifies that according to the records, the above-mentioned name was living in the same household together at the address stated above.',
            `This certification is issued upon the request of the interested party as ${purpose} and for whatever legal intent this may serve.`,
            `Issued this ${dateText} at ${_b.full_address}. Philippines.`,
          ],
          signatureLabel: 'Attested by:',
          signatureTitle: _b.captain_title,
        },
        indigency: {
          title: 'CERTIFICATE OF INDIGENCY',
          salutation: 'To Whom It May Concern,',
          paragraphs: [
            `This is to certify that ${name}, legal age, ${civil.toLowerCase()}, bonafide resident of ${address} and are identified belonging to the "Indigent family" in this community as per record in this office.`,
            `This further certifies that the above-named and whose family earned meager income not enough to augment their basic needs and financial, hence an indigent and qualified to avail for ${purpose}.`,
            `Given this ${dateText} at ${_b.full_address}, Philippines.`,
          ],
          signatureLabel: 'Attested by:',
          signatureTitle: _b.captain_title,
        },
        good_moral: {
          title: 'CERTIFICATE OF GOOD MORAL CHARACTER',
          salutation: 'TO WHOM IT MAY CONCERN:',
          paragraphs: [
            `This is to certify that ${name}, legal age, ${civil.toLowerCase()}, is a bonafide resident of ${address}.`,
            'This further certifies that the above-named person is known to this office as a person of good moral character, law-abiding and with no derogatory record on file as of this date.',
            `This certification is issued upon the request of the interested party for ${purpose} and for whatever legal purpose it may serve.`,
            `Issued this ${dateText} at ${_b.full_address}, Philippines.`,
          ],
          signatureLabel: 'Attested by:',
          signatureTitle: _b.captain_title,
        },
        first_time_job_seeker: {
          title: 'FIRST TIME JOB SEEKER CERTIFICATE',
          salutation: 'TO WHOM IT MAY CONCERN,',
          paragraphs: [
            `This is to certify that ${name}, a bonafide resident of ${address}, is a first-time jobseeker under Republic Act No. 11261.`,
            'This certification is issued to support the applicant\'s exemption from fees for government documents required for employment purposes.',
            `Issued this ${dateText} at ${_b.full_address}.`,
          ],
          signatureLabel: 'Attested by:',
          signatureTitle: _b.captain_title,
        },
        solo_parent: {
          title: 'SOLO PARENT CERTIFICATE',
          salutation: 'TO WHOM IT MAY CONCERN,',
          paragraphs: [
            `This is to certify that ${name}, a bonafide resident of ${address}, is registered in this barangay as a solo parent.`,
            'This certification is issued upon the request of the interested party for government benefits and other lawful purposes.',
            `Issued this ${dateText} at ${_b.full_address}.`,
          ],
          signatureLabel: 'Attested by:',
          signatureTitle: _b.captain_title,
        },
        business_permit: {
          title: 'BUSINESS PERMIT CLEARANCE',
          salutation: 'TO WHOM IT MAY CONCERN,',
          paragraphs: [
            `This is to certify that ${name} is a bonafide resident of ${address}.`,
            `This clearance is issued upon the request of the interested party in connection with a business permit application for ${purpose}.`,
            `Issued this ${dateText} at ${_b.full_address}.`,
          ],
          signatureLabel: 'Approved by:',
          signatureTitle: _b.captain_title,
        },
        other_document: {
          title: 'BARANGAY CERTIFICATION',
          salutation: 'TO WHOM IT MAY CONCERN,',
          paragraphs: [
            `This is to certify that ${name} is a bonafide resident of ${address}.`,
            `This certification is issued upon the request of the interested party for ${purpose}.`,
            `Issued this ${dateText} at ${_b.full_address}.`,
          ],
          signatureLabel: 'Attested by:',
          signatureTitle: _b.captain_title,
        },
      };

      return _contentOverride || defaults[docKey] || defaults.clearance;
    }

    function editableBody(content, boldValues = []) {
      const paragraphs = Array.isArray(content.paragraphs) ? content.paragraphs : [];
      return paragraphs.filter(paragraph => String(paragraph).trim() !== '')
        .map(paragraph => {
          let formattedText = escapeText(paragraph).replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
          boldValues
            .filter(value => String(value ?? '').trim() !== '')
            .forEach(value => {
              const safeValue = escapeText(value);
              formattedText = formattedText.replaceAll(safeValue, `<strong>${safeValue}</strong>`);
            });
          return `<p class="bc-indent">${formattedText}</p>`;
        })
        .join('\n');
    }

    function clearanceFooter(d) {
      return `<div class="bc-footer-info" style="margin-top:auto;">
    <p>CTC No.: _______________</p>
    <p>Issued at: <strong>${escapeText(_b.full_address)}</strong></p>
    <p>Issued on: <strong>${escapeText(d.month + ' ' + d.day + ', ' + d.year)}</strong></p>
    <div class="bc-photo-row"><div class="bc-photo-box"></div><div class="bc-photo-box"></div></div>
    <p>OR. No.: _______________</p>
    <p>Issued at: <strong>${escapeText(_b.full_address)}</strong></p>
    <p>Issued on: <strong>${escapeText(d.month + ' ' + d.day + ', ' + d.year)}</strong></p>
  </div>`;
    }

    /**
     * Set immutable snapshot values for an already-approved document.
     * Call this immediately before build() or print() for approved requests.
     * Pass null for either field to fall back to the live value.
     *
     * @param {string|null} captainName  e.g. 'EMILIANO S BATOY'
     * @param {string|null} issuedDate   ISO date string e.g. '2026-08-20'
     */
    function setSnapshot(captainName, issuedDate) {
        _snapshot.captain_name = captainName || null;
        _snapshot.issued_date  = issuedDate  || null;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    function ordinal(n) {
        const s = ['th', 'st', 'nd', 'rd'], v = n % 100;
        return n + (s[(v - 20) % 10] || s[v] || s[0]);
    }

    function today() {
        const d = new Date();
        return {
            day:   d.getDate(),
            month: d.toLocaleString('default', { month: 'long' }),
            year:  d.getFullYear(),
        };
    }

    // ── Shared screen-preview CSS ─────────────────────────────────────────────
    const SCREEN_STYLES = `<style>
.bc-wrap{font-family:'Cambria',serif;font-size:14px;color:#111;background:#f0f0f0;display:flex;justify-content:center;width:794px;height:1123px;padding:20px;box-sizing:border-box;overflow:hidden;}
.bc-page{width:100%;height:100%;background:#fff;border:2px solid #3a6abf;box-sizing:border-box;display:flex;flex-direction:column;position:relative;overflow:hidden;}
.bc-top-box{border-bottom:2px solid #3a6abf;padding:20px 40px 0;}
.bc-header-row{display:flex;align-items:flex-start;justify-content:center;gap:32px;padding-bottom:14px;}
.bc-seal{width:90px;height:90px;object-fit:contain;border-radius:50%;}
.bc-header-center{text-align:center;font-family:'Times New Roman',serif;font-size:11px;font-weight:bold;line-height:1.5;color:#111;}
.bc-header-center p{margin:0;}.bc-oOo{font-weight:normal!important;font-style:italic;}
.bc-office-bar{font-family:'Times New Roman',serif;font-size:13px;font-weight:bold;color:#111;padding:8px 0 10px;text-align:center;}
.bc-body-box{flex:1;padding:28px 48px 36px;position:relative;overflow:hidden;}
.bc-watermark{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0.06;pointer-events:none;z-index:0;}
.bc-watermark img{width:150mm;height:150mm;object-fit:contain;}
.bc-doc-title{text-align:center;font-family:'Times New Roman',serif;font-size:20px;font-weight:700;margin-bottom:28px;position:relative;z-index:1;}
.bc-body-text{font-family:'Times New Roman',serif;font-size:14px;position:relative;z-index:1;line-height:2;text-align:justify;}
.bc-body-text p{margin:0 0 14px;}.bc-indent{text-indent:3em;}
.bc-line{font-weight:700;}
.bc-line-name,.bc-line-address,.bc-line-date,.bc-line-month{font-weight:700;}
.bc-sig-section{display:flex;justify-content:space-between;align-items:flex-start;margin:48px 0 32px;position:relative;z-index:1;}
.bc-sig-left{min-width:200px;}.bc-sig-line{border-bottom:1px solid #111;width:190px;margin-bottom:4px;}
.bc-sig-sub{font-size:12px;color:#444;}.bc-sig-right{text-align:center;margin-top:8px;}
.bc-approved-by{margin:0 0 4px;font-size:14px;}.bc-captain-name{margin:0;padding-top:10px;font-weight:700;font-size:15px;letter-spacing:.3px;}
.bc-captain-title{margin:0;font-size:13px;color:#333;}
.bc-footer-info{margin-top:auto;font-family:'Times New Roman',serif;position:relative;z-index:1;font-size:12px;line-height:1.6;}
.bc-footer-info p{margin:0;}.bc-photo-row{display:flex;gap:12px;margin:10px 0;}
.bc-photo-box{width:90px;height:80px;border:1px solid #555;}
</style>`;

    // ── Shared print CSS ──────────────────────────────────────────────────────
    const PRINT_STYLES = `<style>
@page { size: A4 portrait; margin: 0; }
* { box-sizing: border-box; margin: 0; padding: 0; }
html, body { width: 210mm; height: 297mm; background: #fff; }
.bc-wrap { width: 210mm; height: 297mm; padding: 7mm; display: block; overflow: hidden; }
.bc-page {
  width: 100%; height: 100%;
  border: 2.5px solid #3a6abf;
  display: flex; flex-direction: column;
  position: relative; overflow: hidden;
  padding: 0;
}
.bc-watermark {
  position: absolute; top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  opacity: 0.07; pointer-events: none; z-index: 0;
}
.bc-watermark img { width: 150mm; height: 150mm; object-fit: contain; }
.bc-top-box { border-bottom: 2px solid #3a6abf; padding: 5.3mm 10.6mm 0; flex-shrink: 0; }
.bc-header-row { display: flex; align-items: flex-start; justify-content: center; gap: 8.5mm; padding-bottom: 3.7mm; }
.bc-seal { width: 24mm; height: 24mm; object-fit: contain; border-radius: 50%; }
.bc-header-center { text-align: center; font-family: 'Times New Roman', serif; font-size: 8.25pt; font-weight: bold; line-height: 1.5; }
.bc-header-center p { margin: 0; }
.bc-oOo { font-weight: normal !important; font-style: italic; }
.bc-office-bar { text-align: center; font-family: 'Times New Roman', serif; font-size: 9.75pt; font-weight: bold; padding: 2.1mm 0 2.6mm; flex-shrink: 0; }
.bc-body-box { flex: 1; display: flex; flex-direction: column; padding: 7.4mm 12.7mm 9.5mm; position: relative; }
.bc-doc-title { text-align: center; font-family: 'Times New Roman', serif; font-size: 15pt; font-weight: bold; margin: 7.4mm 0 7.4mm; position: relative; z-index: 1; flex-shrink: 0; }
.bc-body-text { font-family: 'Times New Roman', serif; font-size: 10.5pt; line-height: 2; text-align: justify; position: relative; z-index: 1; flex: 1; }
.bc-body-text p { margin: 0 0 3.7mm; }
.bc-indent { text-indent: 3em; }
.bc-sig-section { display: flex; justify-content: space-between; align-items: flex-start; margin: 12.7mm 0 8.5mm; padding-top: 0; position: relative; z-index: 1; flex-shrink: 0; }
.bc-sig-left { min-width: 180px; }
.bc-sig-line { border-bottom: 1px solid #111; width: 48mm; margin-bottom: 1mm; }
.bc-sig-sub { font-size: 9pt; color: #444; }
.bc-sig-right { text-align: center; margin-top: 8mm; }
.bc-approved-by { margin: 0 0 1mm; font-size: 10.5pt; }
.bc-captain-name { margin: 0; padding-top: 6mm; font-weight: bold; font-size: 11.25pt; }
.bc-captain-title { margin: 0; font-size: 9.75pt; color: #333; }
.bc-footer-info { font-family: 'Times New Roman', serif; font-size: 9pt; line-height: 1.6; position: relative; z-index: 1; flex-shrink: 0; margin-top: auto; }
.bc-footer-info p { margin: 0; }
.bc-photo-row { display: flex; gap: 3.2mm; margin: 2.6mm 0; }
.bc-photo-box { width: 24mm; height: 21mm; border: 1px solid #555; }
.bc-indigency-sig { text-align: right; font-family: Cambria, serif; font-size: 13pt; margin-top: auto; padding-top: 20mm; position: relative; z-index: 1; flex-shrink: 0; line-height: 1.7; }
.bc-indigency-sig p { margin: 0; }
.bc-not-valid { font-family: Cambria, serif; font-size: 13pt; font-weight: normal; font-style: normal; color: #c0392b; margin-top: 10mm; position: relative; z-index: 1; flex-shrink: 0; }
.bc-residency-sig { display: flex; justify-content: flex-end; margin-top: auto; padding-top: 20mm; position: relative; z-index: 1; flex-shrink: 0; }
.bc-residency-sig div { text-align: center; min-width: 220px; font-family: Cambria, serif; font-size: 13pt; line-height: 1.7; }
</style>`;

    // ── Shared header HTML — uses dynamic barangay settings ───────────────────
    function _header() {
        return `<div class="bc-top-box">
  <div class="bc-header-row">
    <img src="/bacolod.png" class="bc-seal" alt="${_b.barangay_name} Seal">
    <div class="bc-header-center">
      <p>${_b.country}</p>
      <p>${_b.region}</p>
      <p>${_b.province}</p>
      <p>${_b.municipality}</p>
      <p><strong>${_b.barangay_name}</strong></p>
      <p class="bc-oOo">-oOo-</p>
    </div>
    <img src="/Picture1.png" class="bc-seal" alt="Bato Seal">
  </div>
  <div class="bc-office-bar"><strong>${_b.office_header}</strong></div>
</div>`;
    }

    // ── Document body builders ────────────────────────────────────────────────

    function _clearance(name, civil, zone, purpose, d) {
        const zoneText = zone ? zone + ', ' : '';
      const content = editableContent('clearance', name, civil, zone, purpose, d);
        if (_contentOverride) return `<div class="bc-doc-title">${escapeText(content.title)}</div>
  <div class="bc-body-text"><p><strong>${escapeText(content.salutation)}</strong></p>${editableBody(content, [name, civil, purpose, zone, _b.full_address, d.month + ' ' + d.year])}</div>
      <div class="bc-sig-section" style="justify-content:flex-end;"><div class="bc-sig-right"><p class="bc-approved-by">${escapeText(content.signatureLabel)}</p><p class="bc-captain-name" style="padding-top: 12mm;">${escapeText(_b.captain_name)}</p><p class="bc-captain-title">${escapeText(content.signatureTitle)}</p></div></div>${clearanceFooter(d)}`;
        return `<div class="bc-doc-title">BARANGAY CLEARANCE</div>
<div class="bc-body-text">
  <p><strong>TO WHOM IT MAY CONCERN,</strong></p>
      <p class="bc-indent">This is to certify that <strong>${name}</strong>, <strong>a legal age</strong>, <strong>${civil}</strong> and a bonafide resident of <strong>${zoneText}${_b.full_address}</strong>.</p>
  <p class="bc-indent">He/She possessed good moral character, trustworthy, a law-abiding Filipino Citizen and cooperative to all undertakings for the progress of the community.</p>
  <p class="bc-indent">This Barangay Clearance is being issued upon the request of the above-named person for <strong>${purpose}</strong> and for whatever legal purposes it may serve.</p>
      <p class="bc-indent">Given this <strong>${ordinal(d.day)}</strong> day of <strong>${d.month}, ${d.year}</strong> at <strong>${_b.full_address}, Philippines.</strong></p>
</div>
<div class="bc-sig-section">
  <div class="bc-sig-left"><div class="bc-sig-line"></div><div class="bc-sig-sub">(Signature of Applicant)</div></div>
  <div class="bc-sig-right">
    <p class="bc-approved-by">Approved by:</p>
    <p class="bc-captain-name" style="padding-top: 12mm;">${_b.captain_name}</p>
    <p class="bc-captain-title">${_b.captain_title}</p>
  </div>
</div>
  <div class="bc-footer-info" style="margin-top: auto;">
    <p>CTC No.: _______________</p>
    <p>Issued at: <strong>${_b.full_address}</strong></p>
    <p>Issued on: <strong>${d.month} ${d.day}, ${d.year}</strong></p>
    <div class="bc-photo-row"><div class="bc-photo-box"></div><div class="bc-photo-box"></div></div>
    <p>OR. No.: _______________</p>
    <p>Issued at: <strong>${_b.full_address}</strong></p>
    <p>Issued on: <strong>${d.month} ${d.day}, ${d.year}</strong></p>
  </div>
`;
    }

    function _residency(name, civil, zone, purpose, d) {
        const zoneText = zone ? zone + ', ' : '';
      const content = editableContent('residency', name, civil, zone, purpose, d);
      if (_contentOverride) return `<div class="bc-doc-title" style="color:#1a3a8f;">${escapeText(content.title)}</div>
  <div class="bc-body-text"><p><strong>${escapeText(content.salutation)}</strong></p>${editableBody(content, [name, civil, purpose, zone, _b.full_address, d.month + ' ' + d.year])}</div>
  <div class="bc-sig-section" style="justify-content:flex-end;margin-top:auto;padding-top:18mm;"><div style="text-align:center;min-width:220px;font-family:Cambria,serif;font-size:13pt;line-height:1.7;"><p style="margin:0;">${escapeText(content.signatureLabel)}</p><p style="margin:0;padding-top:12mm;font-weight:700;">${escapeText(_b.captain_name)}</p><p style="margin:0;">${escapeText(content.signatureTitle)}</p></div></div>`;
        return `<div class="bc-doc-title" style="color:#1a3a8f;">BARANGAY CERTIFICATION</div>
<div class="bc-body-text">
  <p><strong>TO WHOM IT MAY CONCERN:</strong></p>
  <p class="bc-indent">This is to certify that <strong>${name}</strong>, both of legal age, ${civil} Filipino and a Bonafide resident of ${zoneText}<strong>${_b.full_address}.</strong></p>
  <p class="bc-indent">This further certifies that according to the records, the above-mentioned name was living in the same household together at the address stated above.</p>
  <p class="bc-indent">This certification is issued upon the request of the interested party as <strong>${purpose}</strong> and for whatever legal intent this may serve.</p>
  <p class="bc-indent">Issued this <strong>${ordinal(d.day)}</strong> day of <strong>${d.month}, ${d.year}</strong> at <strong>${_b.full_address}. Philippines.</strong></p>
</div>
<div class="bc-sig-section" style="justify-content:flex-end;margin-top:auto;padding-top:18mm;">
  <div style="text-align:center;min-width:220px;font-family:Cambria,serif;font-size:13pt;line-height:1.7;">
    <p style="margin:0;">Attested by:</p>
    <p style="margin:0;padding-top:12mm;font-weight:700;text-decoration:underline;">${_b.captain_name}</p>
    <p style="margin:0;">${_b.captain_title}</p>
  </div>
</div>`;
    }

    function _indigency(name, civil, zone, purpose, d) {
        const zoneText = zone ? zone + ', ' : '';
      const content = editableContent('indigency', name, civil, zone, purpose, d);
      if (_contentOverride) return `<div class="bc-doc-title" style="color:#1a3a8f;">${escapeText(content.title)}</div>
  <div class="bc-body-text" style="flex:1;"><p><strong>${escapeText(content.salutation)}</strong></p>${editableBody(content, [name, civil, purpose, zone, _b.full_address, d.month + ' ' + d.year])}</div>
  <div style="margin-top:auto;padding-top:20mm;text-align:right;font-family:Cambria,serif;font-size:12pt;position:relative;z-index:1;line-height:1.7;flex-shrink:0;"><p style="margin:0;">${escapeText(content.signatureLabel)}</p><p style="margin:0;padding-top:12mm;font-weight:700;">${escapeText(_b.captain_name)}</p><p style="margin:0;">${escapeText(content.signatureTitle)}</p></div>`;
        return `<div class="bc-doc-title" style="color:#1a3a8f;">CERTIFICATE OF INDIGENCY</div>
<div class="bc-body-text" style="flex:1;">
  <p><strong>To Whom It May Concern,</strong></p>
  <p class="bc-indent">This is to certify that <strong>${name}</strong>, legal age, ${civil.toLowerCase()}, bonafide resident of ${zoneText}<strong>${_b.full_address}</strong> and are identified belonging to the "Indigent family" in this community as per record in this office.</p>
  <p class="bc-indent">This further certifies that the above-named and whose family earned meager income not enough to augment their basic needs and financial, hence an indigent and qualified to avail for <strong>${purpose}</strong>.</p>
  <p class="bc-indent">Given this <strong>${ordinal(d.day)}</strong> day of <strong>${d.month}, ${d.year}</strong> at <strong>${_b.full_address}, Philippines.</strong></p>
</div>
<div style="margin-top:auto;padding-top:20mm;text-align:right;font-family:Cambria,serif;font-size:12pt;position:relative;z-index:1;line-height:1.7;flex-shrink:0;">
  <p style="margin:0;">Attested by:</p>
  <p style="margin:0;padding-top:12mm;font-weight:700;text-decoration:underline;">${_b.captain_name}</p>
  <p style="margin:0;">${_b.captain_title}</p>
</div>
<p style="margin-top:10mm;font-family:Cambria,serif;font-size:12pt;color:#c0392b;position:relative;z-index:1;flex-shrink:0;">Not Valid Without Seal</p>`;
    }

    function _goodMoral(name, civil, zone, purpose, d) {
        const content = editableContent('good_moral', name, civil, zone, purpose, d);
        if (_contentOverride) return `<div class="bc-doc-title" style="color:#1a3a8f;">${escapeText(content.title)}</div>
  <div class="bc-body-text" style="flex:1;"><p><strong>${escapeText(content.salutation)}</strong></p>${editableBody(content, [name, civil, purpose, zone, _b.full_address, d.month + ' ' + d.year])}</div>
  <div style="margin-top:auto;padding-top:20mm;text-align:right;font-family:Cambria,serif;font-size:12pt;position:relative;z-index:1;line-height:1.7;flex-shrink:0;"><p style="margin:0;">${escapeText(content.signatureLabel)}</p><p style="margin:0;padding-top:12mm;font-weight:700;">${escapeText(_b.captain_name)}</p><p style="margin:0;">${escapeText(content.signatureTitle)}</p></div>`;

        const zoneText = zone ? zone + ', ' : '';
        return `<div class="bc-doc-title" style="color:#1a3a8f;">CERTIFICATE OF GOOD MORAL CHARACTER</div>
<div class="bc-body-text" style="flex:1;">
  <p><strong>TO WHOM IT MAY CONCERN:</strong></p>
  <p class="bc-indent">This is to certify that <strong>${name}</strong>, legal age, ${civil.toLowerCase()}, is a bonafide resident of ${zoneText}<strong>${_b.full_address}</strong>.</p>
  <p class="bc-indent">This further certifies that the above-named person is known to this office as a person of good moral character, law-abiding and with no derogatory record on file as of this date.</p>
  <p class="bc-indent">This certification is issued upon the request of the interested party for <strong>${purpose}</strong> and for whatever legal purpose it may serve.</p>
  <p class="bc-indent">Issued this <strong>${ordinal(d.day)}</strong> day of <strong>${d.month}, ${d.year}</strong> at <strong>${_b.full_address}, Philippines.</strong></p>
</div>
<div style="margin-top:auto;padding-top:20mm;text-align:right;font-family:Cambria,serif;font-size:12pt;position:relative;z-index:1;line-height:1.7;flex-shrink:0;">
  <p style="margin:0;">Attested by:</p>
  <p style="margin:0;padding-top:12mm;font-weight:700;text-decoration:underline;">${_b.captain_name}</p>
  <p style="margin:0;">${_b.captain_title}</p>
</div>`;
    }

    function _special(docKey, name, civil, zone, purpose, d) {
      const content = editableContent(docKey, name, civil, zone, purpose, d);
      if (_contentOverride) {
        return `<div class="bc-doc-title" style="color:#1a3a8f;">${escapeText(content.title)}</div><div class="bc-body-text" style="flex:1;"><p><strong>${escapeText(content.salutation)}</strong></p>${editableBody(content, [name, civil, purpose, zone, _b.full_address, d.month + ' ' + d.year])}</div><div style="margin-top:auto;padding-top:20mm;text-align:right;font-family:Cambria,serif;font-size:12pt;position:relative;z-index:1;"><p>${escapeText(content.signatureLabel)}</p><p style="padding-top:12mm;font-weight:700;">${escapeText(_b.captain_name)}</p><p>${escapeText(content.signatureTitle)}</p></div>`;
      }
      return `<div class="bc-doc-title" style="color:#1a3a8f;">${escapeText(content.title)}</div><div class="bc-body-text" style="flex:1;"><p><strong>${escapeText(content.salutation)}</strong></p>${content.paragraphs.map(paragraph => `<p class="bc-indent">${escapeText(paragraph)}</p>`).join('')}</div><div style="margin-top:auto;padding-top:20mm;text-align:right;font-family:Cambria,serif;font-size:12pt;position:relative;z-index:1;"><p>${escapeText(content.signatureLabel)}</p><p style="padding-top:12mm;font-weight:700;">${escapeText(_b.captain_name)}</p><p>${escapeText(content.signatureTitle)}</p></div>`;
    }

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Resolve the effective date for a document.
     * Uses the snapshot's issued_date if set, otherwise today.
     */
    function _resolveDate() {
        if (_snapshot.issued_date) {
            const parts = _snapshot.issued_date.split('-');
            const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
            return {
                day:   d.getDate(),
                month: d.toLocaleString('default', { month: 'long' }),
                year:  d.getFullYear(),
            };
        }
        return today();
    }

    /**
     * Resolve the effective captain name for a document.
     * Uses the snapshot's captain_name if set, otherwise the live _b.captain_name.
     */
    function _resolveCaptain() {
        return _snapshot.captain_name || _b.captain_name;
    }

    /**
     * Clear the snapshot after use so the next pending document
     * gets the live captain name and today's date.
     */
    function _clearSnapshot() {
        _snapshot.captain_name = null;
        _snapshot.issued_date  = null;
    }

    /**
     * Build a screen-preview HTML string.
     * @param {string} docKey   'clearance' | 'residency' | 'indigency'
     * @param {string} forMember  Name of the person the document is for
     * @param {string} purpose    Purpose of the request
     * @returns {string} Full HTML including styles
     */
    function build(docKey, forMember, purpose) {
        const name    = forMember || _census.name;
        const civil   = _census.civil || 'Single';
        const zone    = _census.zone  || '';
        const d       = _resolveDate();
        const captain = _resolveCaptain();
        _clearSnapshot();

        // Temporarily override captain_name for this render
        const savedCaptain = _b.captain_name;
        _b.captain_name = captain;

        let body = '';
        if      (docKey === 'clearance') body = _clearance(name, civil, zone, purpose, d);
        else if (docKey === 'residency') body = _residency(name, civil, zone, purpose, d);
        else if (docKey === 'good_moral') body = _goodMoral(name, civil, zone, purpose, d);
        else if (['first_time_job_seeker', 'solo_parent', 'business_permit', 'other_document'].includes(docKey)) body = _special(docKey, name, civil, zone, purpose, d);
        else                             body = _indigency(name, civil, zone, purpose, d);

        _b.captain_name = savedCaptain;

        return `<div class="bc-wrap"><div class="bc-page">
  ${_header()}
  <div class="bc-body-box">
    <div class="bc-watermark"><img src="/bacolod.png" alt="watermark"></div>
    ${body}
  </div>
</div></div>${SCREEN_STYLES}`;
    }

    /**
     * Open a print window with the document.
     * @param {string} docKey
     * @param {string} forMember
     * @param {string} purpose
     */
    function print(docKey, forMember, purpose) {
        const name    = forMember || _census.name;
        const civil   = _census.civil || 'Single';
        const zone    = _census.zone  || '';
        const d       = _resolveDate();
        const captain = _resolveCaptain();
        _clearSnapshot();

        const savedCaptain = _b.captain_name;
        _b.captain_name = captain;

        let body = '';
        if      (docKey === 'clearance') body = _clearance(name, civil, zone, purpose, d);
        else if (docKey === 'residency') body = _residency(name, civil, zone, purpose, d);
        else if (docKey === 'good_moral') body = _goodMoral(name, civil, zone, purpose, d);
        else if (['first_time_job_seeker', 'solo_parent', 'business_permit', 'other_document'].includes(docKey)) body = _special(docKey, name, civil, zone, purpose, d);
        else                             body = _indigency(name, civil, zone, purpose, d);

        _b.captain_name = savedCaptain;

        const html = `<!DOCTYPE html><html><head><title>Document</title>${PRINT_STYLES}</head>
<body>
<div class="bc-wrap"><div class="bc-page">
  ${_header()}
  <div class="bc-body-box">
    <div class="bc-watermark"><img src="/bacolod.png" alt="watermark"></div>
    ${body}
  </div>
</div></div>
<script>window.onload=function(){window.print();window.close();}<\/script>
</body></html>`;

        const win = window.open('', '_blank', 'width=900,height=900');
        win.document.write(html);
        win.document.close();
    }

    const api = {
        setCensus,
        setCaptain,
        setBarangay,
        setSnapshot,
        setContent,
        editableContent,
        normalizeBodyText,
        parseBodyText,
        build,
        print,
    };

    if (typeof window !== 'undefined') {
        window.BisDoc = api;
    }
    if (typeof globalThis !== 'undefined') {
        globalThis.BisDoc = api;
    }

    return api;

})();
