@extends('layouts.app')

@section('title', 'OCR Screenshot Upload')

@section('content')
    <div style="max-width:860px; margin:0 auto; padding:24px 20px; font-family:'Nunito',sans-serif;">

        {{-- Page Header --}}
        <div style="margin-bottom:24px;">
            <h1 style="font-size:22px; font-weight:900; color:#1E1033; margin:0 0 4px;">📸 Screenshot OCR Processing</h1>
            <p style="font-size:13px; color:#7C6FAB; margin:0;">Upload game screenshots to automatically extract character
                stats, training data, race results, and skills.</p>
        </div>

        {{-- Circuit Breaker Status --}}
        <div id="ocr-status-card"
            style="background:#F0FDF4; border:1px solid #6EE7B7; border-radius:12px; padding:12px 16px; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
            <div
                style="width:10px; height:10px; border-radius:50%; background:#10B981; box-shadow:0 0 6px #10B981; flex-shrink:0;">
            </div>
            <div>
                <div style="font-size:13px; font-weight:800; color:#065F46;">OCR System Status</div>
                <span style="font-size:12px; color:#065F46;">External APIs healthy · OCR pipeline available</span>
            </div>
        </div>

        {{-- Character + Data Type Selection --}}
        <div
            style="background:#fff; border:1px solid #EDE9FE; border-radius:16px; padding:20px 24px; margin-bottom:20px; box-shadow:0 2px 12px rgba(124,58,237,0.07);">
            <div style="font-size:15px; font-weight:800; color:#1E1033; margin-bottom:16px;">⚙️ Configuration</div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div>
                    <label for="character-select"
                        style="display:block; font-size:12px; font-weight:700; color:#7C6FAB; margin-bottom:6px; text-transform:uppercase; letter-spacing:1px;">Select
                        Character</label>
                    <select id="character-select"
                        style="width:100%; padding:10px 14px; border:1px solid #EDE9FE; border-radius:10px; font-size:13px; color:#1E1033; background:#F9F5FF; outline:none; box-sizing:border-box;"
                        aria-label="Select character for OCR data import">
                        <option value="">— Select a character —</option>
                        @foreach ($characters as $character)
                            <option value="{{ $character->id }}">{{ $character->name }}
                                ({{ ucfirst($character->scenario_type) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="data-type-select"
                        style="display:block; font-size:12px; font-weight:700; color:#7C6FAB; margin-bottom:6px; text-transform:uppercase; letter-spacing:1px;">Data
                        Type</label>
                    <select id="data-type-select"
                        style="width:100%; padding:10px 14px; border:1px solid #EDE9FE; border-radius:10px; font-size:13px; color:#1E1033; background:#F9F5FF; outline:none; box-sizing:border-box;"
                        aria-label="Select type of data to extract from screenshot">
                        <option value="auto">Auto-detect</option>
                        <option value="character_stats">Character Stats</option>
                        <option value="training_session">Training Session</option>
                        <option value="race_result">Race Result</option>
                        <option value="skill_list">Skill List</option>
                        <option value="support_card">Support Card Info</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Drop Zone --}}
        <div id="drop-zone-wrapper" style="margin-bottom:20px;">
            <div id="drop-zone"
                style="border:2px dashed #C4B5FD; border-radius:20px; padding:60px 40px; text-align:center; background:#F9F5FF; cursor:pointer; transition:all 0.15s;"
                role="button" tabindex="0" aria-label="Drag and drop screenshots or click to select files"
                aria-describedby="upload-format-info">
                <div style="font-size:48px; margin-bottom:12px;">📸</div>
                <p style="font-size:16px; font-weight:700; color:#1E1033; margin:0 0 6px;">Drag and drop screenshots here
                </p>
                <p style="font-size:13px; color:#7C6FAB; margin:0 0 20px;">or click to browse files</p>
                <button type="button" id="browse-button"
                    style="padding:10px 24px; border-radius:10px; font-size:13px; font-weight:700; background:linear-gradient(135deg,#E879A0,#7C3AED); color:#fff; border:none; cursor:pointer; box-shadow:0 4px 12px rgba(232,121,160,.35);">
                    Select File
                </button>
                <input type="file" id="file-input" style="display:none;"
                    accept="image/jpeg,image/jpg,image/png,image/webp" multiple aria-label="Select screenshot files">
                <div id="upload-format-info" style="margin-top:16px; font-size:11px; color:#7C6FAB;">
                    Supported: JPG, PNG, WEBP · Max <span id="max-file-size">10 MB</span> per file
                </div>
            </div>
        </div>

        {{-- Upload Queue --}}
        <div id="upload-queue" style="display:none; margin-bottom:20px;">
            <div
                style="background:#fff; border:1px solid #EDE9FE; border-radius:16px; padding:20px 24px; box-shadow:0 2px 12px rgba(124,58,237,0.07);">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                    <div style="font-size:15px; font-weight:800; color:#1E1033;">Upload Queue</div>
                    <div style="display:flex; gap:8px;">
                        <button id="process-all-button"
                            style="padding:8px 18px; border-radius:10px; font-size:13px; font-weight:700; background:linear-gradient(135deg,#E879A0,#7C3AED); color:#fff; border:none; cursor:pointer; opacity:0.5;"
                            disabled>
                            Process All
                        </button>
                        <button id="clear-queue-button"
                            style="padding:8px 14px; border-radius:10px; font-size:13px; font-weight:700; background:#EDE9FE; color:#7C3AED; border:none; cursor:pointer;">
                            Clear
                        </button>
                    </div>
                </div>
                <div id="queue-items" style="display:flex; flex-direction:column; gap:10px;"></div>
            </div>
        </div>

        {{-- Pipeline Progress (shown during processing) --}}
        <div id="pipeline-progress" style="display:none; margin-bottom:20px;">
            <div
                style="background:#fff; border:1px solid #EDE9FE; border-radius:16px; padding:20px 24px; box-shadow:0 2px 12px rgba(124,58,237,0.07);">
                <div style="font-size:15px; font-weight:800; color:#1E1033; margin-bottom:16px;">🔄 Processing Pipeline
                </div>
                <div style="display:flex; flex-direction:column; gap:10px;" id="pipeline-steps">
                    @php
                        $steps = [
                            ['id' => 'step-preprocess', 'label' => 'Image Preprocessor', 'icon' => '🖼️'],
                            ['id' => 'step-ocr', 'label' => 'Tesseract OCR Engine', 'icon' => '🔍'],
                            ['id' => 'step-parse', 'label' => 'OCR Data Parser', 'icon' => '📊'],
                            ['id' => 'step-validate', 'label' => 'OCR Validator', 'icon' => '✅'],
                        ];
                    @endphp
                    @foreach ($steps as $step)
                        <div id="{{ $step['id'] }}"
                            style="background:#F9F5FF; border:1px solid #EDE9FE; border-radius:12px; padding:12px 16px; display:flex; align-items:center; gap:12px; position:relative; overflow:hidden;">
                            <div
                                style="width:36px; height:36px; border-radius:10px; background:#EDE9FE; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
                                {{ $step['icon'] }}</div>
                            <span style="font-size:13px; font-weight:700; color:#7C6FAB;">{{ $step['label'] }}</span>
                            <div class="pipeline-shimmer"
                                style="display:none; position:absolute; inset:0; background:linear-gradient(90deg,transparent,rgba(124,58,237,0.08),transparent); animation:slide 1.5s infinite;">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Processing Results --}}
        <div id="processing-results" style="display:none;">
            <div
                style="background:#fff; border:1px solid #EDE9FE; border-radius:16px; padding:20px 24px; box-shadow:0 2px 12px rgba(124,58,237,0.07);">
                <div style="font-size:15px; font-weight:800; color:#1E1033; margin-bottom:16px;">📋 Extraction Results</div>
                <div id="results-container" style="display:flex; flex-direction:column; gap:12px;"></div>
            </div>
        </div>

    </div>

    <style>
        @keyframes slide {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(200%);
            }
        }

        #drop-zone:hover,
        #drop-zone.drag-over {
            border-color: #E879A0;
            background: #FDF2F8;
        }

        #drop-zone.drag-over {
            border-color: #E879A0 !important;
            background: #FDF2F8 !important;
        }
    </style>

    @vite(['resources/js/pages/ocr/upload.js'])
@endsection
