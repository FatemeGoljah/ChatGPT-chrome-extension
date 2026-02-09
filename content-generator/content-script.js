function htmlToMarkdown(html) {
  let md = html;

  // هدرها
  md = md.replace(/<h2>(.*?)<\/h2>/g, '## $1\n');
  md = md.replace(/<h3>(.*?)<\/h3>/g, '### $1\n');

  // بولد و ایتالیک
  md = md.replace(/<(strong|b)>(.*?)<\/\1>/g, '**$2**');
  md = md.replace(/<(em|i)>(.*?)<\/\1>/g, '*$2*');

  // لیست‌های نامرتب
  md = md.replace(/<ul>(.*?)<\/ul>/gs, (match, content) => {
    return content.replace(/<li>(.*?)<\/li>/g, '- $1\n');
  });

  // لیست‌های مرتب
  md = md.replace(/<ol>(.*?)<\/ol>/gs, (match, content) => {
    let i = 1;
    return content.replace(/<li>(.*?)<\/li>/g, () => i++ + '. $1\n');
  });

  // پاراگراف‌ها و <br>
  md = md.replace(/<p>(.*?)<\/p>/g, '$1\n');
  md = md.replace(/<br\s*\/?>/g, '\n');

  // جایگزینی نیم‌فاصله
  md = md.replace(/\u200C/g, ' ');

  // پاک کردن تگ‌های باقی مانده
  md = md.replace(/<\/?[^>]+(>|$)/g, '');

  return md.trim();
}





function getLastAssistantMessage() {
  const assistantDivs = Array.from(document.querySelectorAll('[data-message-author-role="assistant"]'));
  if (assistantDivs.length === 0) {
    console.warn('❌ هیچ پیام assistant پیدا نشد.');
    return null;
  }

  const lastAssistantDiv = assistantDivs[assistantDivs.length - 1];
  const markdownDiv = lastAssistantDiv.querySelector('.markdown');
  if (!markdownDiv) return null;

  const htmlContent = markdownDiv.innerHTML;
  const markdownText = htmlToMarkdown(htmlContent);

  return markdownText || null;
}


function getLastAssistantMessageHTML() {
  const assistantDivs = Array.from(document.querySelectorAll('[data-message-author-role="assistant"]'));
  if (assistantDivs.length === 0) return null;

  const lastAssistantDiv = assistantDivs[assistantDivs.length - 1];
  const markdownDiv = lastAssistantDiv.querySelector('.markdown');
  if (!markdownDiv) return null;

  return markdownDiv.innerHTML; // HTML کامل شامل h2, h3, ul, ol, strong و غیره
}

// استفاده در textarea یا مستقیم در contenteditable
const contentField = document.querySelector('#content'); // یا contenteditable div
const htmlContent = getLastAssistantMessageHTML();
if (contentField && htmlContent) {
  contentField.innerHTML = htmlContent; // قالب‌بندی حفظ می‌شود
}


// گوش دادن به پیام‌ها از popup
chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
  console.log('📩 پیام دریافت شد از popup:', request);

  if (request.action === 'getAssistantMessage') {
    const message = getLastAssistantMessage();
    sendResponse({message});
  }
});

// تست اینکه script لود شده
console.log('✅ content-script.js لود شد.');
