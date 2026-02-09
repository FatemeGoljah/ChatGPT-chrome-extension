// ▶ ارسال پرامپت به ChatGPT
document.getElementById("sendPrompt").addEventListener("click", async () => {
  const title = document.getElementById("postTitle").value.trim();
  const keyword = document.getElementById("keywordInput").value.trim();
  const status = document.getElementById("status");

  if (!title || !keyword) {
    status.innerText = "لطفاً عنوان و کلمه کلیدی را وارد کنید.";
    return;
  }

  status.innerText = "⏳ در حال ارسال پرامپت به چت‌جی‌پی‌تی...";

  const fullPrompt = `Please ignore all previous instructions. You are an expert copywriter who creates content briefs. 
You have a Creative writing style. Please write only in the persian language. The article title is "${title}". 
First print out "Content Brief for ${keyword}" as a heading. Then print a heading "Content Overview". 
Then print "Title" and write the article title. After this print "Meta Description". 
Now generate a meta description for the article title that is less than 160 characters. 
The description should contain the keyword "${keyword}". After this table print out the following 
"Outline / What is this content about". Generate a content outline for the article "${title}" here. 
After this print the following "What keywords and topics are recommended or required?" as a heading. 
Now list down 10 keywords that are closely related to "${keyword}". 
After this print the following "What key questions do readers have that need to be answered?" as a heading. 
Now generate 10 questions that the reader may have related to the ${title} and ${keyword} and print them out. 
Do not self reference. Do not explain what you are doing.`;

  const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });

  chrome.scripting.executeScript({
    target: { tabId: tab.id },
    func: sendPromptToChatGPTAndGetResponse,
    args: [fullPrompt],
  }, (results) => {
    if (chrome.runtime.lastError || !results || !results[0]?.result) {
      return;
    }
    const chatContent = results[0].result;
    document.getElementById("postContent").value = chatContent;
  });
});


// ▶ این تابع در خود صفحه چت اجرا می‌شه
function sendPromptToChatGPTAndGetResponse(promptText) {
  return new Promise((resolve) => {
    const editableDiv = document.querySelector('div.ProseMirror[contenteditable="true"]');
    if (!editableDiv) {
      alert("🛑 باکس چت پیدا نشد.");
      resolve(null);
      return;
    }

    // پاک‌سازی قبلی و وارد کردن پرامپت
    editableDiv.focus();
    editableDiv.innerHTML = `<p>${promptText.replace(/\n/g, "<br>")}</p>`;
    editableDiv.dispatchEvent(new InputEvent("input", { bubbles: true }));

    const tryClickingSend = () => {
      const sendButton = document.querySelector('button[data-testid="send-button"]');
      if (sendButton) {
        sendButton.click();
        waitForResponse(resolve);
      } else {
        setTimeout(tryClickingSend, 300);
      }
    };

    tryClickingSend();

    // تابعی که منتظر می‌مونه پاسخ ظاهر بشه
    function waitForResponse(resolveFn) {
      const checkInterval = setInterval(() => {
        const messages = Array.from(document.querySelectorAll("div.markdown"));
        if (messages.length > 500) {
          clearInterval(checkInterval);
          const lastResponse = messages[messages.length - 1].innerText;
          resolveFn(lastResponse);
        }
      }, 2000); // هر ۲ ثانیه چک می‌کنه
    }
  });
}


function savePostData() {
  const postTitle = document.getElementById("postTitle").value;
  const keywords = document.getElementById("keywords").value;
  const postContent = document.getElementById("postContent").value;
  const seoText = document.getElementById("seoText").value;


  chrome.storage.local.set({ postTitle,keywords, postContent,seoText });
}

document.getElementById("postTitle").addEventListener("input", savePostData);
document.getElementById("postContent").addEventListener("input", savePostData);
document.getElementById("keywords").addEventListener("input", savePostData);

document.getElementById("seoText").addEventListener("input", savePostData);

document.addEventListener("DOMContentLoaded", () => {
  chrome.storage.local.get(["postTitle","keywords", "postContent","seoText"], data => {
    if (data.postTitle) document.getElementById("postTitle").value = data.postTitle;
    if (data.postContent) document.getElementById("postContent").value = data.postContent;
        if (data.seoText) document.getElementById("seoText").value = data.seoText;
    if (data.keywords) document.getElementById("keywords").value = data.keywords;

    
  });
});
document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll(".tab");
  const tabContents = {
    chat: document.getElementById("chatContent"),
    preview: document.getElementById("previewContent"),
    seobox: document.getElementById("seobox"),
  };

  tabs.forEach(tab => {
    tab.addEventListener("click", () => {
      // حذف کلاس active از همه تب‌ها
      tabs.forEach(t => t.classList.remove("active"));
      // اضافه کردن active به تب کلیک شده
      tab.classList.add("active");

      // نمایش محتوای مرتبط، مخفی کردن بقیه
      const target = tab.getAttribute("data-tab");
      Object.entries(tabContents).forEach(([key, contentDiv]) => {
        contentDiv.style.display = key === target ? "block" : "none";
      });
    });
  });
});

//fetchBtn
document.addEventListener('DOMContentLoaded', () => {
  const fetchBtn = document.getElementById('fetchBtn');
  const postContentInput = document.getElementById('postContent');

  // بارگذاری متن ذخیره شده هنگام باز شدن popup
  chrome.storage.local.get(['assistantMessages'], (result) => {
    if (result.assistantMessages) {
      postContentInput.value = result.assistantMessages;
      postContentInput.scrollTop = postContentInput.scrollHeight;
    }
  });

  // ذخیره خودکار تغییرات هنگام تایپ یا پیست کردن
  postContentInput.addEventListener('input', () => {
    chrome.storage.local.set({ assistantMessages: postContentInput.value });
  });

  fetchBtn.addEventListener('click', () => {
    postContentInput.value += '\n\n';

    chrome.tabs.query({ active: true, currentWindow: true }, tabs => {
      if (!tabs[0]) {
        postContentInput.value += '\n❌ تب فعال پیدا نشد.';
        return;
      }

      chrome.tabs.sendMessage(tabs[0].id, { action: 'getAssistantMessage' }, response => {
        if (chrome.runtime.lastError) {
          postContentInput.value += '\n❌ خطا در ارتباط با صفحه: ' + chrome.runtime.lastError.message;
          return;
        }

        if (!response || !response.message) {
          postContentInput.value += '\n❌ هیچ پیام assistant پیدا نشد.';
          return;
        }

        // جایگزینی نیم‌فاصله‌ها با فاصله
        let newMessage = response.message.replace(/\u200C/g, ' ');

        // اضافه کردن پیام جدید به انتهای متن قبلی
        postContentInput.value += '\n\n' + newMessage;

        // ذخیره متن در storage
        chrome.storage.local.set({ assistantMessages: postContentInput.value });

        // اسکرول خودکار به پایین textarea
        postContentInput.scrollTop = postContentInput.scrollHeight;
      });
    });
  });
});







//hook
document.getElementById("hook").addEventListener("click", async () => {
  const title = document.getElementById("postTitle").value.trim();
  const keywords = document.getElementById("keywords").value.trim();
  const status = document.getElementById("status");


  if (!title || !keywords) {
    status.innerText = "لطفاً عنوان و کلمه کلیدی ها را وارد کنید.";
    return;
  }

  status.innerText = "⏳ در حال ارسال hook  به چت‌جی‌پی‌تی...";

  const hookPrompt = `
  Please ignore all previous instructions. You are an expert copywriter who creates content briefs. 
You have a Creative writing style. Please write only in the persian language.
   "${title}" با لحن مناسب اما ساده و بسیار روان بدون اینکه شعار بدی برام یه قلاب مقدمه ای شکل بنویس که خواننده رو تشویق بکنه متن رو کامل بخونه برای `;

  const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });

  chrome.scripting.executeScript({
    target: { tabId: tab.id },
    func: sendPromptToChatGPTAndGetResponse,
    args: [hookPrompt],
  }, (results) => {
    if (chrome.runtime.lastError || !results || !results[0]?.result) {
      return;
    }
    const chatContent = results[0].result;
  });
});


//meta
document.getElementById("meta").addEventListener("click", async () => {
  const title = document.getElementById("postTitle").value.trim();
  const keywords = document.getElementById("keywords").value.trim();
  const status = document.getElementById("status");


  if (!title || !keywords) {
    status.innerText = "لطفاً عنوان و کلمه کلیدی ها را وارد کنید.";
    return;
  }

  status.innerText = "⏳ در حال ارسال meta  به چت‌جی‌پی‌تی...";

  const metaPrompt = `
  Please ignore all previous instructions. You are an expert copywriter who creates content briefs. 
You have a Creative writing style. Please write only in the persian language.
 You are an expert SEO and content writer. I will give you a single keyword, and I want you to create:

1. A short, attractive SEO-friendly title (50-60 characters) including the main keyword.
2. A meta description in Persian (maximum 155 characters) including the keyword.

Output only in JSON format, no extra text.  
Example format:

  "title": "SEO-friendly title in Persian",
  "metaDescription": "Meta description in Persian"


Keyword: "${keywords}"  
Instruction: Write both the title and metaDescription in Persian.



 `;

  const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });

  chrome.scripting.executeScript({
    target: { tabId: tab.id },
    func: sendPromptToChatGPTAndGetResponse,
    args: [metaPrompt],
  }, (results) => {
    if (chrome.runtime.lastError || !results || !results[0]?.result) {
      return;
    }
    const chatContent = results[0].result;
    document.getElementById("seoText").value = chatContent;
  });
});


//step 2
document.getElementById("step2").addEventListener("click", async () => {
  const title = document.getElementById("postTitle").value.trim();
  const keywords = document.getElementById("keywords").value.trim();
  const brand = document.getElementById("brand").value.trim();

  
  const status = document.getElementById("status");


  if (!title || !keywords) {
    status.innerText = "لطفاً عنوان و کلمه کلیدی ها را وارد کنید.";
    return;
  }

  status.innerText = "⏳ در حال ارسال step2  به چت‌جی‌پی‌تی...";

  const step2p1 = `
  حالا بهم بگو کسی که ${title} سرچ میکنه دنبال چه چیزی هست؟`;

const step2p3 = `
  میخوام بر همین اساس برام تتیرهای کاربری رو بنویسی

حالا هر تیتری رو که احساس میکنی محتواش تکراری میشه و خیلی شبیه بهمه حذف بکن و با توجه به کلمات کلیدی اصلی متن شروع کن به تولید محتوا برای هر تیتیر`;
  const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });

  chrome.scripting.executeScript({
    target: { tabId: tab.id },
    func: sendPromptToChatGPTAndGetResponse,
    args: [step2p1],
  }, (results) => {
    if (chrome.runtime.lastError || !results || !results[0]?.result) {
      return;
    }
    const chatContent = results[0].result;
    document.getElementById("seoText").value = chatContent;
  });

 chrome.scripting.executeScript({
    target: { tabId: tab.id },
    func: sendPromptToChatGPTAndGetResponse,
    args: [step2p3],
  }, (results) => {
    if (chrome.runtime.lastError || !results || !results[0]?.result) {
      return;
    }
    const chatContent = results[0].result;
    document.getElementById("seoText").value = chatContent;
  });

  
});


//step 3
document.getElementById("step3").addEventListener("click", async () => {
  const title = document.getElementById("postTitle").value.trim();
  const keywords = document.getElementById("keywords").value.trim();
  const status = document.getElementById("status");


  if (!title || !keywords) {
    status.innerText = "لطفاً عنوان و کلمه کلیدی ها را وارد کنید.";
    return;
  }

  status.innerText = "⏳ در حال ارسال step 3  به چت‌جی‌پی‌تی...";

  const step3 = `
 بر همین اساس ادامه مقاله رو بنویس
   `;

    const step3p2 = `
ادامه بده   `;
  const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });

  chrome.scripting.executeScript({
    target: { tabId: tab.id },
    func: sendPromptToChatGPTAndGetResponse,
    args: [step3],
  }, (results) => {
    if (chrome.runtime.lastError || !results || !results[0]?.result) {
      return;
    }
    const chatContent = results[0].result;
    document.getElementById("seoText").value = chatContent;
  });
    chrome.scripting.executeScript({
    target: { tabId: tab.id },
    func: sendPromptToChatGPTAndGetResponse,
    args: [step3p2],
  }, (results) => {
    if (chrome.runtime.lastError || !results || !results[0]?.result) {
      return;
    }
    const chatContent = results[0].result;
    document.getElementById("seoText").value = chatContent;
  });
});

//permalink
 let link = ''; // اینجا آخرین پیام ذخیره میشه
document.getElementById("permalink").addEventListener("click", async () => {
  const title = document.getElementById("postTitle").value.trim();
  const keywords = document.getElementById("keywords").value.trim();
  const status = document.getElementById("status");


  if (!title) {
    status.innerText = "لطفاً عنوان  را وارد کنید.";
    return;
  }

  status.innerText = "⏳ در حال ارسال permalink  به چت‌جی‌پی‌تی...";

  const permalinkPrompt = `
Translate the following Persian phrase into natural English and convert it into a clean, SEO-friendly URL slug. 

Requirements:
- Use meaningful English translation (not transliteration). 
- Only lowercase letters. 
- Replace spaces with hyphens (-). 
- Remove all special characters, numbers, or extra symbols. 
- Output only the URL slug, nothing else. Do not include quotes, boxes, or any additional text.


Phrase:  "${title}"
   `;

  const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });

  chrome.scripting.executeScript({
    target: { tabId: tab.id },
    func: sendPromptToChatGPTAndGetResponse,
    args: [permalinkPrompt],
  }, (results) => {
    if (chrome.runtime.lastError || !results || !results[0]?.result) {
      return;
    }
    const chatContent = results[0].result;
    document.getElementById("seoText").value = chatContent;
  });

// گرفتن آخرین پیام assistant از storage
  chrome.storage.local.get(['assistantMessages'], (result) => {
    if (result.assistantMessages) {
      link = result.assistantMessages; // ذخیره آخرین پیام در متغیر link
      console.log('آخرین پیام assistant:', link);
        status.innerText = link;

    } else {
      console.log('هیچ پیام assistant پیدا نشد.');
    }
    
  });

});






//metaTextBtn
let metaFieldValue = ''; // متغیر سراسری
let titleField, metaField;
let parsedData = null;

document.addEventListener('DOMContentLoaded', () => {
  const metaTextBtn = document.getElementById('metaTextBtn');
  const metaText = document.getElementById('metaText');

  chrome.storage.local.get(['assistantMessages'], (result) => {
    if (result.assistantMessages) {
      metaText.value = result.assistantMessages;
      metaText.scrollTop = metaText.scrollHeight;
      metaFieldValue = result.assistantMessages;
    }
  });

  metaText.addEventListener('input', () => {
    metaFieldValue = metaText.value;
    chrome.storage.local.set({ assistantMessages: metaFieldValue });
  });

  metaTextBtn.addEventListener('click', () => {
    metaText.value = '\n';

    chrome.tabs.query({ active: true, currentWindow: true }, tabs => {
      if (!tabs[0]) {
        metaText.value = '\n❌ تب فعال پیدا نشد.';
        return;
      }

      chrome.tabs.sendMessage(tabs[0].id, { action: 'getAssistantMessage' }, response => {
        if (chrome.runtime.lastError) {
          metaText.value = '\n❌ خطا در ارتباط با صفحه: ' + chrome.runtime.lastError.message;
          return;
        }

        if (!response || !response.message) {
          metaText.value = '\n❌ هیچ پیام assistant پیدا نشد.';
          return;
        }

        let newMessage = response.message.replace(/\u200C/g, ' ');
        metaText.value = newMessage;
        metaFieldValue = newMessage;

        chrome.storage.local.set({ assistantMessages: metaFieldValue });
        metaText.scrollTop = metaText.scrollHeight;
      });
    });
  });

  //metagenrator
  const metagenrator = document.getElementById('metagenrator');
  metagenrator.addEventListener('click', () => {
     let gptOutput = metaFieldValue;


  // 🔹 پیدا کردن اولین { و آخرین } برای گرفتن JSON خالص
  const firstBrace = gptOutput.indexOf('{');
  const lastBrace = gptOutput.lastIndexOf('}');

  if (firstBrace === -1 || lastBrace === -1) {
    console.error('JSON پیدا نشد');
    return;
  }

  // 🔹 گرفتن فقط متن بین { و } و ساخت JSON صحیح
  const cleanJson = gptOutput.slice(firstBrace, lastBrace + 1);

  try {
    parsedData = JSON.parse(cleanJson);
  } catch (err) {
    console.error('خطا در پارس کردن JSON:', err);
    console.log('متن پاکسازی شده برای JSON:', cleanJson);
    return;
  }
 
  // اگر بخوای همینجا توی Rank Math هم بنویسه:
  const titleInput = document.querySelector('#rank-math-editor-title');
  const metaInput  = document.querySelector('#rank-math-editor-description');

  if (titleInput) {
    titleInput.value = parsedData.title || '';
    titleInput.dispatchEvent(new Event('input', { bubbles: true }));
  }
  if (metaInput) {
    metaInput.value = parsedData.metaDescription || '';
    metaInput.dispatchEvent(new Event('input', { bubbles: true }));
  }
  });
});

// ▶ ارسال به وردپرس
const postTitleInput = document.getElementById('postTitle');
const postContentInput = document.getElementById('postContent');
const permalinkInput =link;
const sendBtn = document.getElementById('sendBtn');
const statusDiv = document.getElementById('status');

sendBtn.addEventListener('click', () => {
 if (!parsedData || typeof parsedData !== "object") {
    statusDiv.textContent = '⚠️ لطفاً اول دکمه تولید متا را بزنید.';
    return;
  }
  const titleValue = (parsedData.title || '').trim();
  const metaValue  = (parsedData.metaDescription || '').trim();

  const title   = (postTitleInput.value || '').trim();
  const content = (postContentInput.value || '').trim();
  const permalink   = (permalinkInput.value || '').trim();


 
  if (!title || !content) {
    statusDiv.textContent = '⚠️ لطفاً همه فیلدها را پر کنید.';
    return;
  }
  sendBtn.disabled = true;
  statusDiv.textContent = '🚀 در حال ارسال به وردپرس...';


  fetch("https://24om.ir/content-generator.php", {
    method: "POST",
    body: new URLSearchParams({
      title,
      content,
      permalink,
      titleField: titleValue,
  metaField: metaValue
    }),
  })
  .then(response => response.json())
  .then(data => {
    if (data.id) {
      statusDiv.textContent = `✅ نوشته ذخیره شد: ID ${data.id}`;
    } else if (data.message) {
      statusDiv.textContent = `❌ خطا: ${data.message}`;
    } else {
      statusDiv.textContent = '❌ خطای ناشناخته';
    }
  })
  .catch(err => {
    statusDiv.textContent = '❌ خطای ارتباطی: ' + err.message;
  })
  .finally(() => {
    sendBtn.disabled = false;
  });
});

